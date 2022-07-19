<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Filters\Exception\FilterException;

interface FilterProviderInterface
{
    /**
     * @throws FilterException
     */
    public function createFilter(string $name, InputInterface $input): FilterInterface;
}
