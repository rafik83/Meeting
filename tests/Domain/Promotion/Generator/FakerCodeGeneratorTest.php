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

        var_dump($generator->generate($event));

        $this->assertRegExp('', $generator->generate($event));
    }

    public function testIfGeneratedCodeIsRandom()
    {
        // generer plusieur code et vérifier qu'ils sont tous différent
    }
}


// static ne peut etre testée, donc pas de mock
// dans un premier temps verifier que la fonction donne quelque chose avec 6 chiffres et en maj
// verfier sur deux ou trois tours que l'on ne génère pas le même code, aléatoire

