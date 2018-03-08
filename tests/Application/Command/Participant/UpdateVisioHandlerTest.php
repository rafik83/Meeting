<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Participant\UpdateVisio;
use Proximum\Vimeet\Application\Command\Participant\UpdateVisioHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateVisioHandlerTest extends TestCase
{
    public function testHandle()
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $sheet = new Sheet($event, $type, [], $user, $now);

        $participant = new Participant(
            $sheet,
            $user,
            [],
            false
        );

        $expectedParticipant = new Participant(
            $sheet,
            $user,
            [],
            false
        );
        $expectedParticipant->setVisio(true);

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $command = new UpdateVisio($participant, true);

        $participantRepository->set($expectedParticipant)->shouldBeCalled();

        $handler = new UpdateVisioHandler($participantRepository->reveal());

        $handler->handle($command);
    }
}
