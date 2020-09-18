<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

interface GrammarInterface
{
    /**
     * Generate stream of tokens or pass generation to overlay grammar.
     *
     * @param Buffer $src
     * @return \Generator|Token[]|Byte[]
     */
    public function parse(Buffer $src): \Generator;

    /**
     * Return unique token name for the given grammar.
     *
     * @param int $token
     * @return string
     */
    public static function tokenName(int $token): string;
}
