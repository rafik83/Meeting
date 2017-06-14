<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Code;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Code\DigitCodeGenerator;

class DigitCodeGeneratorTest extends TestCase
{
    public function testGenerateCode()
    {
        $generator = new DigitCodeGenerator();

        for ($i = 0; $i < 100; $i++) {
            $code = $generator->generateCode(4);
            $code9chars = $generator->generateCode(9);

            $this->assertEquals(4, strlen($code));
            $this->assertEquals(9, strlen($code9chars));
            $this->assertNotContains($code, [
                '0000',
                '1111',
                '2222',
                '3333',
                '4444',
                '5555',
                '6666',
                '7777',
                '8888',
                '9999',
            ]);
        }
    }
}
