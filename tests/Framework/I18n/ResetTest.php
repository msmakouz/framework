<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\I18n;

use Spiral\Tests\Framework\ConsoleTestCase;

final class ResetTest extends ConsoleTestCase
{
    public function testReset(): void
    {
        $this->runCommand('i18n:index');
        $this->assertConsoleCommandOutputContainsStrings('i18n:reset', strings: 'cache has been reset');
    }
}
