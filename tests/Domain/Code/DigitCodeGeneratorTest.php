<?php

namespace Proximum\Vimeet\Tests\Domain\Code;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Code\DigitCodeGenerator;

class DigitCodeGeneratorTest extends TestCase
{
    public function testGenerateCode()
    {
        $generator = new DigitCodeGenerator();

        for ($i = 0; $i < 100; ++$i) {
            $code4chars = $generator->generateCode(4);
            $code9chars = $generator->generateCode(9);

            $this->assertEquals(4, strlen($code4chars));
            $this->assertEquals(9, strlen($code9chars));
        }
    }

    public function testIsExcluded()
    {
        $generator = new DigitCodeGenerator();

        $reflection = new \ReflectionClass(DigitCodeGenerator::class);
        $method = $reflection->getMethod('isExcluded');
        $method->setAccessible(true);

        $this->assertTrue($method->invokeArgs($generator, ['0000']));
        $this->assertTrue($method->invokeArgs($generator, ['1111']));
        $this->assertTrue($method->invokeArgs($generator, ['2222']));
        $this->assertTrue($method->invokeArgs($generator, ['3333']));
        $this->assertTrue($method->invokeArgs($generator, ['4444']));
        $this->assertTrue($method->invokeArgs($generator, ['5555']));
        $this->assertTrue($method->invokeArgs($generator, ['6666']));
        $this->assertTrue($method->invokeArgs($generator, ['7777']));
        $this->assertTrue($method->invokeArgs($generator, ['8888']));
        $this->assertTrue($method->invokeArgs($generator, ['9999']));
        $this->assertTrue($method->invokeArgs($generator, ['00000000000']));
        $this->assertFalse($method->invokeArgs($generator, ['1234']));
        $this->assertFalse($method->invokeArgs($generator, ['4321']));
        $this->assertFalse($method->invokeArgs($generator, ['9876']));
        $this->assertFalse($method->invokeArgs($generator, ['6789']));
        $this->assertFalse($method->invokeArgs($generator, ['4567']));
        $this->assertFalse($method->invokeArgs($generator, ['123456789']));
        $this->assertFalse($method->invokeArgs($generator, ['987654321']));
        $this->assertFalse($method->invokeArgs($generator, ['0000000000000001']));
        $this->assertFalse($method->invokeArgs($generator, ['1111111111121111']));

        $method->setAccessible(false);
    }
}
