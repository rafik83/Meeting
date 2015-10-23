<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\User;

use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Command\User\ParticipateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = new Event();
        $type  = new Type();
        $owner = true;

        $expectedSheet       = new Sheet($event, $type);
        $expectedParticipant = new Participant($expectedSheet, $user, ['foobar' => 'barfoo'], $owner);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->add($expectedSheet)->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $handler = new ParticipateHandler($sheetRepository->reveal(), $participantRepository->reveal());
        $handler->handle(new Participate($user, $event, $type, ['foobar' => 'barfoo']));
    }
}
