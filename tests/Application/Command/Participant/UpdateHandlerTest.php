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
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Application\Components\Template\Validator;
use Proximum\Vimeet\Application\Exception\Participant\UpdateNotAllowedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = new Event();
        $type  = new Type($event);
        $type->setParticipantTemplate([
            'foobar' => [
                'required' => true,
                'private'  => false,
            ]
        ]);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());
        $owner = true;

        $participant         = new Participant($sheet, $user, ['foobar' => 'barfoo'], $owner, true);
        $expectedParticipant = new Participant($sheet, $user, ['foobar' => 'foobar'], $owner, true);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->set($expectedParticipant)->shouldBeCalled();

        $participantManager = $this->prophesize(ParticipantManager::class);
        $participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)->shouldBeCalled()->willReturn(true);

        $validator = $this->prophesize(Validator::class);

        $update  = new Update($sheet, $user, $participant);
        $update->data = ['foobar' => 'foobar'];
        $handler = new UpdateHandler($participantRepository->reveal(), $participantManager->reveal(), $validator->reveal());
        $handler->handle($update);
    }

    public function testUpdateNotAllowedException()
    {
        $this->expectException(UpdateNotAllowedException::class);

        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = new Event();
        $type  = new Type($event);
        $type->setParticipantTemplate([
            'foobar' => [
                'required' => true,
                'private'  => false,
            ]
        ]);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());
        $owner = true;

        $participant = new Participant($sheet, $user, ['foobar' => 'barfoo'], $owner, true);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->set()->shouldNotBeCalled();

        $participantManager = $this->prophesize(ParticipantManager::class);
        $participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)->shouldBeCalled()->willReturn(false);

        $validator = $this->prophesize(Validator::class);

        $update  = new Update($sheet, $user, $participant);
        $update->data = ['foobar' => 'foobar'];
        $handler = new UpdateHandler($participantRepository->reveal(), $participantManager->reveal(), $validator->reveal());
        $handler->handle($update);
    }
}
