<?php

namespace Proximum\Vimeet\Tests\Domain\Token;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Token\UniqidGenerator;

class UniqidGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $generator = new UniqidGenerator();

        $array = [
            $generator->generate(),
            $generator->generate(),
            $generator->generate(),
            $generator->generate(),
            $generator->generate(),
            $generator->generate(),
            $generator->generate(),
            $generator->generate(),
            $generator->generate(),
            $generator->generate(),
        ];

        $arrayUnique = array_unique($array);

        $this->assertEquals($array, $arrayUnique);
        $this->assertNotContains(uniqid(mt_rand()), $array);
    }
}
