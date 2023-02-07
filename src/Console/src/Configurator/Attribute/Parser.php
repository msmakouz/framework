<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator\Attribute;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;
use Spiral\Console\Configurator\Result;
use Spiral\Console\Exception\ConfiguratorException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final class Parser
{
    public function __construct(
        private readonly ReaderInterface $reader = new AttributeReader()
    ) {
    }

    public function hasCommandAttribute(\ReflectionClass $reflection): bool
    {
        return $this->reader->firstClassMetadata($reflection, AsCommand::class) !== null;
    }

    public function parse(\ReflectionClass $reflection): Result
    {
        /** @var AsCommand $attribute */
        $attribute = $this->reader->firstClassMetadata($reflection, AsCommand::class);

        return new Result(
            name: $attribute->name,
            arguments: $this->parseArguments($reflection),
            options: $this->parseOptions($reflection),
            description: $attribute->description,
            help: $attribute->help
        );
    }

    public function fillProperties(Command $command, InputInterface $input): void
    {
        $reflection = new \ReflectionClass($command);

        foreach ($reflection->getProperties() as $property) {
            $attribute = $this->reader->firstPropertyMetadata($property, Argument::class);
            if ($attribute === null) {
                continue;
            }

            if ($input->hasArgument($attribute->name ?? $property->getName())) {
                $property->setValue(
                    $command,
                    $this->typecast($input->getArgument($attribute->name ?? $property->getName()), $property)
                );
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $attribute = $this->reader->firstPropertyMetadata($property, Option::class);
            if ($attribute === null) {
                continue;
            }

            if ($input->hasOption($attribute->name ?? $property->getName())) {
                $property->setValue(
                    $command,
                    $this->typecast($input->getOption($attribute->name ?? $property->getName()), $property)
                );
            }
        }
    }

    private function parseArguments(\ReflectionClass $reflection): array
    {
        $result = [];
        $arrayArgument = null;
        foreach ($reflection->getProperties() as $property) {
            $attribute = $this->reader->firstPropertyMetadata($property, Argument::class);
            if ($attribute === null) {
                continue;
            }

            $type = $this->getPropertyType($property);

            $isOptional = $property->hasDefaultValue() || $type->allowsNull();
            $isArray = $type->getName() === 'array';
            $mode = match (true) {
                $isArray && !$isOptional => InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                $isArray && $isOptional => InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                $isOptional => InputArgument::OPTIONAL,
                default => InputArgument::REQUIRED
            };

            $argument = new InputArgument(
                name: $attribute->name ?? $property->getName(),
                mode: $mode,
                description: (string) $attribute->description,
                default: $property->hasDefaultValue() ? $property->getDefaultValue() : null,
                suggestedValues: $attribute->suggestedValues
            );

            if ($arrayArgument !== null && $isArray) {
                throw new ConfiguratorException('There must be only one array argument!');
            }

            // It must be used at the end of the argument list.
            if ($isArray) {
                $arrayArgument = $argument;
                continue;
            }
            $result[] = $argument;
        }

        if ($arrayArgument !== null) {
            $result[] = $arrayArgument;
        }

        return $result;
    }

    private function parseOptions(\ReflectionClass $reflection): array
    {
        $result = [];
        foreach ($reflection->getProperties() as $property) {
            $attribute = $this->reader->firstPropertyMetadata($property, Option::class);
            if ($attribute === null) {
                continue;
            }

            $type = $this->getPropertyType($property);

            if ($attribute->mode === InputOption::VALUE_NONE || $attribute->mode === InputOption::VALUE_NEGATABLE) {
                if ($type->getName() !== 'bool') {
                    throw new ConfiguratorException(
                        'Options properties with mode `VALUE_NONE` or `VALUE_NEGATABLE` must be bool!'
                    );
                }
            }

            $hasDefaultValue = $attribute->mode !== InputOption::VALUE_NONE && $property->hasDefaultValue();

            $result[] = new InputOption(
                name: $attribute->name ?? $property->getName(),
                shortcut: $attribute->shortcut,
                mode: $attribute->mode,
                description: (string) $attribute->description,
                default: $hasDefaultValue ? $property->getDefaultValue() : null,
                suggestedValues: $attribute->suggestedValues
            );
        }

        return $result;
    }

    private function typecast(mixed $value, \ReflectionProperty $property): mixed
    {
        if (!$property->hasType() || !$property->getType()?->isBuiltin()) {
            return $value;
        }

        return match ($property->getType()?->getName()) {
            'int' => (int) $value,
            'string' => (string) $value,
            'bool' => (bool) $value,
            'float' => (float) $value,
            'array' => (array) $value,
            default => $value
        };
    }

    private function getPropertyType(\ReflectionProperty $property): \ReflectionNamedType
    {
        if (!$property->hasType()) {
            throw new ConfiguratorException(
                \sprintf('Please, specify the type for the `%s` property!', $property->getName())
            );
        }

        $type = $property->getType();

        if ($type instanceof \ReflectionIntersectionType) {
            throw new ConfiguratorException(\sprintf('Invalid type for the `%s` property.', $property->getName()));
        }

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $type) {
                if ($type->isBuiltin()) {
                    return $type;
                }
            }
        }

        if (!$type?->isBuiltin()) {
            throw new ConfiguratorException(\sprintf('Invalid type for the `%s` property.', $property->getName()));
        }

        return $type;
    }
}
