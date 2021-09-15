<?php

namespace Proximum\Vimeet\Tests\Application\Adapter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SaltGenerator;

class SaltGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $generator = new SaltGenerator();

        $salt1 = $generator->generate();
        $salt2 = $generator->generate();
        $salt3 = $generator->generate();

        $this->assertInternalType('string', $salt1);
        $this->assertInternalType('string', $salt2);
        $this->assertInternalType('string', $salt3);

        $this->assertNotEquals($salt1, $salt2);
        $this->assertNotEquals($salt2, $salt3);
        $this->assertNotEquals($salt3, $salt1);
    }
}
