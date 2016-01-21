<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Participant\Delete;
use Proximum\Vimeet\Application\Command\Participant\DeleteHandler;
use Proximum\Vimeet\Application\Exception\Participant\IsNotLinkedToSheetException;
use Proximum\Vimeet\Application\Exception\Participant\OwnerCanNotBeDeletedException;
use Proximum\Vimeet\Application\Exception\Participant\IsNotOwnerException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class DeleteHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user1        = new User('test1@test.com', '__SALT__', 'password', 'fr');
        $user2        = new User('test2@test.com', '__SALT__', 'password', 'fr');
        $event        = new Event();
        $type         = new Type($event);
        $sheet        = new Sheet($event, $type, [], []);
        $participant1 = new Participant($sheet, $user1, [], true, true);
        $participant2 = new Participant($sheet, $user2, [], false, true);
        $sheet->getParticipants()->add($participant1);
        $sheet->getParticipants()->add($participant2);

        $delete = new Delete($sheet, $user1, 2);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->findById(2)->shouldBeCalled()->willReturn($participant2);
        $participantRepository->getParticipantForUserAndSheet($user1, $sheet)->shouldBeCalled()->willReturn($participant1);
        $participantRepository->delete($participant2)->shouldBeCalled();

        $handler = new DeleteHandler($participantRepository->reveal());
        $handler->handle($delete);
    }

    public function testIsNotLinkedToSheetException()
    {
        $this->setExpectedException(IsNotLinkedToSheetException::class);

        $user1        = new User('test1@test.com', '__SALT__', 'password', 'fr');
        $user2        = new User('test2@test.com', '__SALT__', 'password', 'fr');
        $event        = new Event();
        $type         = new Type($event);
        $sheet        = new Sheet($event, $type, [], []);
        $participant2 = new Participant($sheet, $user2, [], false, true);
        $sheet->getParticipants()->add($participant2);

        $delete = new Delete($sheet, $user1, 2);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $handler = new DeleteHandler($participantRepository->reveal());
        $handler->handle($delete);
    }

    public function testOwnerCanNotBeDeletedException()
    {
        $this->setExpectedException(OwnerCanNotBeDeletedException::class);

        $user1        = new User('test1@test.com', '__SALT__', 'password', 'fr');
        $user2        = new User('test2@test.com', '__SALT__', 'password', 'fr');
        $event        = new Event();
        $type         = new Type($event);
        $sheet        = new Sheet($event, $type, [], []);
        $participant1 = new Participant($sheet, $user1, [], true, true);
        $participant2 = new Participant($sheet, $user2, [], false, true);
        $sheet->getParticipants()->add($participant1);
        $sheet->getParticipants()->add($participant2);

        $delete = new Delete($sheet, $user1, 1);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->findById(1)->shouldBeCalled()->willReturn($participant1);

        $handler = new DeleteHandler($participantRepository->reveal());
        $handler->handle($delete);
    }

    public function testIsNotOwnerException()
    {
        $this->setExpectedException(IsNotOwnerException::class);

        $user1        = new User('test1@test.com', '__SALT__', 'password', 'fr');
        $user2        = new User('test2@test.com', '__SALT__', 'password', 'fr');
        $event        = new Event();
        $type         = new Type($event);
        $sheet        = new Sheet($event, $type, [], []);
        $participant1 = new Participant($sheet, $user1, [], false, true);
        $participant2 = new Participant($sheet, $user2, [], false, true);
        $sheet->getParticipants()->add($participant1);
        $sheet->getParticipants()->add($participant2);

        $delete = new Delete($sheet, $user1, 2);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->findById(2)->shouldBeCalled()->willReturn($participant2);
        $participantRepository->getParticipantForUserAndSheet($user1, $sheet)->shouldBeCalled()->willReturn($participant1);

        $handler = new DeleteHandler($participantRepository->reveal());
        $handler->handle($delete);
    }
}
