<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Filters\Dto;

use Spiral\Filters\Exception\FilterException;
use Spiral\Filters\InputInterface;

/**
 * Creates filters on demand based on a given name and input.
 */
interface FilterProviderInterface
{
    /**
     * @throws FilterException
     */
    public function createFilter(string $name, InputInterface $input): FilterInterface;
}
