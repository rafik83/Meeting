<?php

/*
* This file is part of the Proximum Vimeet project.
*
* Copyright (C) 2017 Proximum
*
* @author Elao <contact@elao.com>
*/

namespace Proximum\Vimeet\Tests\Domain\Promotion\Generator;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Promotion\Generator\FakerCodeGenerator;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class FakerCodeGeneratorTest extends TestCase
{
    public function testCodeGenerator()
    {
        $event = EventFactory::createEvent();

        $generator = new FakerCodeGenerator();

        $this->assertRegExp('/[A-Z]{6}/', $generator->generate($event));
    }

    public function testIfGeneratedCodeIsRandom()
    {
        $i = 0;

        $event = EventFactory::createEvent();
        $generator = new FakerCodeGenerator();

        $codes = [];

        while ($i++ < 100) {
            $codes[] = $generator->generate($event);
        }

        $uniqueCodes = array_unique($codes);

        $this->assertEquals(count($codes), count($uniqueCodes));
    }
}
