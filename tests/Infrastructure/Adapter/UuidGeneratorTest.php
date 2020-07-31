<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Infrastructure\Adapter\UuidGenerator;

class UuidGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        // Two uuid should not be equal
        $uuidGenerator = new UuidGenerator();

        $a = $uuidGenerator->generate();
        $b = $uuidGenerator->generate();
        $c = $uuidGenerator->generate();
        $d = $uuidGenerator->generate();

        $this->assertNotEquals($a, $b);
        $this->assertNotEquals($a, $c);
        $this->assertNotEquals($a, $d);
        $this->assertNotEquals($b, $c);
        $this->assertNotEquals($b, $d);
        $this->assertNotEquals($c, $d);
    }
}
