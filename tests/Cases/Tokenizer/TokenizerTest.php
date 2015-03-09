<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Tokenizer;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Reflection\FunctionUsage\Argument;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Loader;
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\MemoryCore;

class TokenizerTest extends TestCase
{
    protected $config = array(
        'directories' => array(__DIR__),
        'exclude'     => array('XX')
    );

    public function testClasses()
    {
        $tokenizer = $this->createTokenizer();

        //Direct loading
        $classes = $tokenizer->getClasses();
        $this->assertArrayHasKey(__CLASS__, $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\Inner\ClassD', $classes);

        //Excluded
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassXX', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\Bad_Class', $classes);

        //By class
        $classes = $tokenizer->getClasses('Spiral\Tests\Cases\Tokenizer\Classes\ClassA');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\Inner\ClassD', $classes);

        //By namespace
        $classes = $tokenizer->getClasses(null, 'Spiral\Tests\Cases\Tokenizer\Classes\Inner');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\Inner\ClassD', $classes);

        //By interface
        $classes = $tokenizer->getClasses('Spiral\Tests\Cases\Tokenizer\TestInterface');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\Inner\ClassD', $classes);

        //By trait
        $classes = $tokenizer->getClasses('Spiral\Tests\Cases\Tokenizer\TestTrait');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\Inner\ClassD', $classes);

        //By class
        $classes = $tokenizer->getClasses('Spiral\Tests\Cases\Tokenizer\Classes\ClassB');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Tokenizer\Classes\Inner\ClassD', $classes);
    }

    public function testFileReflection()
    {
        $reflection = $this->createTokenizer()->fileReflection(__FILE__);

        $this->assertContains(__CLASS__, $reflection->getClasses());

        //Self analysis
        $this->assertContains(__CLASS__, $reflection->getConflicts()['classes']);

        $functionUsages = $reflection->functionUsages();

        $functionA = null;
        $functionB = null;

        foreach ($functionUsages as $usage)
        {
            if ($usage->getFunction() == 'test_function_a')
            {
                $functionA = $usage;
            }

            if ($usage->getFunction() == 'test_function_b')
            {
                $functionB = $usage;
            }
        }

        $this->assertNotEmpty($functionA);
        $this->assertNotEmpty($functionB);

        $this->assertSame(2, count($functionA->getArguments()));
        $this->assertSame(Argument::VARIABLE, $functionA->getArgument(0)->getType());
        $this->assertSame('$this', $functionA->getArgument(0)->getValue());

        $this->assertSame(Argument::EXPRESSION, $functionA->getArgument(1)->getType());
        $this->assertSame('$a+$b', $functionA->getArgument(1)->getValue());

        $this->assertSame(2, count($functionB->getArguments()));

        $this->assertSame(Argument::STRING, $functionB->getArgument(0)->getType());
        $this->assertSame('"string"', $functionB->getArgument(0)->getValue());
        $this->assertSame('string', $functionB->getArgument(0)->stringValue());

        $this->assertSame(Argument::CONSTANT, $functionB->getArgument(1)->getType());
        $this->assertSame('123', $functionB->getArgument(1)->getValue());

        if (false)
        {
            $a = $b = null;
            test_function_a($this, $a + $b);
            test_function_b("string", 123);
        }
    }

    protected function createTokenizer($config = null)
    {
        return new Tokenizer(
            MemoryCore::getInstance()->setConfig('tokenizer', $config ?: $this->config),
            new FileManager(),
            new Loader(MemoryCore::getInstance())
        );
    }
}

trait TestTrait
{

}

interface TestInterface
{

}