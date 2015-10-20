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
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $data = [
            'foobar' => 'barfoo'
        ];

        $expectedData = '{"foobar":"barfoo"}';

        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = new Event();
        $type  = new Participant\Type();

        $expectedParticipant = new Participant($user, $event, $type, $expectedData);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $handler = new ParticipateHandler($participantRepository->reveal());
        $handler->handle(new Participate($user, $event, $type, $data));
    }
}
