<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Token;

use Proximum\Vimeet\Domain\Token\UniqidGenerator;

class UniqidGeneratorTest extends \PHPUnit_Framework_TestCase
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
