<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Attribute\Input;

use Spiral\Filters\Attribute\Input\Input;

final class InputTest extends \Spiral\Tests\Filters\AttributeTest
{
    public function testGetsValueForDefinedKey(): void
    {
        $attribute = new Input('foo');

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('input', 'foo')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForDefinedKey(): void
    {
        $attribute = new Input('foo');

        $this->assertSame(
            'input:foo',
            $attribute->getSchema($this->makeProperty())
        );
    }

    public function testGetsValueForNotDefinedKey(): void
    {
        $attribute = new Data();

        $this->input
            ->shouldReceive('getValue')
            ->once()
            ->with('input', 'baz')
            ->andReturn('bar');

        $this->assertSame(
            'bar',
            $attribute->getValue($this->input, $this->makeProperty())
        );
    }

    public function testGetsSchemaForNotDefinedKey(): void
    {
        $attribute = new Data();

        $this->assertSame(
            'input:baz',
            $attribute->getSchema($this->makeProperty())
        );
    }
}
