<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Participant\Update;
use Proximum\Vimeet\Application\Command\Participant\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = new Event();
        $type  = new Type();

        $participant         = new Participant($user, $event, $type, '{"foobar":"barfoo"}');
        $expectedParticipant = new Participant($user, $event, $type, '{"foobar":"foobar"}');

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->findById(1)->shouldBeCalled()->willReturn($participant);
        $participantRepository->set($expectedParticipant)->shouldBeCalled();

        $update  = new Update(1, ['foobar' => 'foobar']);
        $handler = new UpdateHandler($participantRepository->reveal());
        $handler->handle($update);
    }
}
