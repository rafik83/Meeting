<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant;


use Proximum\Vimeet\Application\Command\Participant\UpdateVisio;
use Proximum\Vimeet\Application\Command\Participant\UpdateVisioHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateVisioHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $sheet = new Sheet($event, $type, [], $user, $now);
        $sheet->setRegistrationData([
            "3ad4b72f" => ['text' => 'oldFoo'],
            "9ef18c06" => ['url' => 'http://www.oldfoo.com'],
            "93e093f"  => ['text' => '10 rue de la oldFoo'],
            "de66af5d" => ['text' => '75002'],
            "d224f0e7" => ['text' => 'oldFooVille'],
            "e801edd4" => ['country' => 'EN'],
        ]);
        $participant = new Participant(
            $sheet,
            $user,
            [],
            true
        );

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $participantRepository->set($participant)->shouldBeCalled();

        $handler = new UpdateVisioHandler($participantRepository->reveal());

        $command = new UpdateVisio($participant, true);

        $handler->handle($command);
    }
}
