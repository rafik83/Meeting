<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Participant\Delete;
use Proximum\Vimeet\Application\Command\Participant\DeleteHandler;
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Application\Exception\Participant\DeleteNotAllowedException;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DeleteHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user1        = new User('test1@test.com', '__SALT__', 'password', 'fr');
        $user2        = new User('test2@test.com', '__SALT__', 'password', 'fr');
        $event        = EventFactory::createEvent();
        $type         = new Type($event);
        $sheet        = new Sheet($event, $type, [], $user1, new \DateTime());
        $participant1 = new Participant($sheet, $user1, [], true, true);
        $participant2 = new Participant($sheet, $user2, [], false, true);
        $sheet->getParticipants()->add($participant1);
        $sheet->getParticipants()->add($participant2);

        $delete = new Delete($sheet, $user1, $participant2);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->delete($participant2)->shouldBeCalled();

        $participantManager = $this->prophesize(ParticipantManager::class);
        $participantManager->isUserAllowedToDeleteParticipant($sheet, $participant2, $user1)->shouldBeCalled()->willReturn(true);

        $handler = new DeleteHandler($participantRepository->reveal(), $participantManager->reveal());
        $handler->handle($delete);
    }

    public function testDeleteNotAllowedException()
    {
        $this->expectException(DeleteNotAllowedException::class);
        $user1        = new User('test1@test.com', '__SALT__', 'password', 'fr');
        $user2        = new User('test2@test.com', '__SALT__', 'password', 'fr');
        $event        = EventFactory::createEvent();
        $type         = new Type($event);
        $sheet        = new Sheet($event, $type, [], $user1, new \DateTime());
        $participant1 = new Participant($sheet, $user1, [], true, true);
        $participant2 = new Participant($sheet, $user2, [], false, true);
        $sheet->getParticipants()->add($participant1);
        $sheet->getParticipants()->add($participant2);

        $delete = new Delete($sheet, $user1, $participant2);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->delete()->shouldNotBeCalled();

        $participantManager = $this->prophesize(ParticipantManager::class);
        $participantManager->isUserAllowedToDeleteParticipant($sheet, $participant2, $user1)->shouldBeCalled()->willReturn(false);

        $handler = new DeleteHandler($participantRepository->reveal(), $participantManager->reveal());
        $handler->handle($delete);
    }
}
