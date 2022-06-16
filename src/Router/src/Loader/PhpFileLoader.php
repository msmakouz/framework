<?php

declare(strict_types=1);

namespace Spiral\Router\Loader;

use Spiral\Core\Container;
use Spiral\Router\Exception\LoaderLoadException;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;
use Spiral\Router\RouteCollection;

final class PhpFileLoader implements LoaderInterface
{
    public function __construct(
        private readonly Container $container
    ) {
    }

    /**
     * Loads a PHP file.
     */
    public function load(mixed $resource, string $type = null): RouteCollection
    {
        if (!\file_exists($resource)) {
            throw new LoaderLoadException(\sprintf('File [%s] does not exist.', $resource));
        }

        $load = static function (string $path) {
            return include $path;
        };

        $callback = $load($resource);

        $collection = new RouteCollection();

        $configurator = new RoutingConfigurator($collection, $this->container->make(LoaderInterface::class));

        $args = $this->container->resolveArguments(new \ReflectionFunction($callback), [$configurator]);

        // Compiling routes from callback
        $callback(...$args);

        return $collection;
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return
            \is_string($resource) &&
            \pathinfo($resource, \PATHINFO_EXTENSION) === 'php' &&
            (!$type || $type === 'php');
    }
}
