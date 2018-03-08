<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Sheet\ParticipantFinder;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ParticipantFinderTest extends TestCase
{
    public function testHasParticipantFalse()
    {
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 2);
        $property->setAccessible(false);

        $this->assertEquals(
            false,
            ParticipantFinder::hasParticipantWithId($sheet, 1)
        );
    }

    public function testHasParticipantTrue()
    {
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 2);
        $property->setAccessible(false);

        $this->assertEquals(
            true,
            ParticipantFinder::hasParticipantWithId($sheet, 2)
        );
    }

    public function testGetParticipantNotFound()
    {
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 2);
        $property->setAccessible(false);

        $this->assertEquals(
            null,
            ParticipantFinder::getParticipantWithId($sheet, 1)
        );
    }

    public function testGetParticipant()
    {
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 2);
        $property->setAccessible(false);

        $this->assertEquals(
            $participant,
            ParticipantFinder::getParticipantWithId($sheet, 2)
        );
    }
}
