<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Participant\Remove;
use Proximum\Vimeet\Application\Command\Participant\RemoveHandler;
use Proximum\Vimeet\Application\Command\Participant\RemoveResult;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Required
        $locale = 'fr';
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $owner = new User('email@email.fr', 'password', 'salt', 'fr');
        $user2 = new User('user@email.fr', 'password', 'salt', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $owner, $date);
        $participant1 = new Participant($sheet, $owner, [], true);
        $participant2 = new Participant($sheet, $user2, [], true);
        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $cartManager = $this->prophesize(CartManager::class);

        $eventDispatcher   = $this->prophesize(DelayedEventDispatcher::class);
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(Events::PARTICIPANT_REMOVED, new ParticipantRemovedEvent($sheet))->shouldBeCalled();

        $meetingRepository      = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->countByParticipant($participant1)->shouldBeCalled()->willReturn(0);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant1, $locale)->shouldNotBeCalled();

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant2);

        $cartManager->updateParticipantsQuantity($sheet)->shouldBeCalled();

        // Command
        $remove = new Remove($sheet, $locale);
        $remove->participants = [
            $participant1
        ];

        // Handle
        $handler = new RemoveHandler(
            $participantRepository->reveal(),
            $cartManager->reveal(),
            $eventDispatcher->reveal(),
            $meetingRepository->reveal(),
            $participantInfoGuesser->reveal()
        );
        $result         = $handler->handle($remove);
        $expectedResult = new RemoveResult();

        $this->assertEquals($expectedSheet->countParticipants(), $sheet->countParticipants());
        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleWithMeeting()
    {
        // Required
        $locale = 'fr';
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $owner = new User('email@email.fr', 'password', 'salt', 'fr');
        $user2 = new User('user@email.fr', 'password', 'salt', 'fr');
        $user3 = new User('user3@email.fr', 'password', 'salt', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $owner, $date);
        $participant1 = new Participant($sheet, $owner, [], true);
        $participant2 = new Participant($sheet, $user2, [], true);
        $participant3 = new Participant($sheet, $user3, [], true);
        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);
        $sheet->addParticipant($participant3);

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $cartManager = $this->prophesize(CartManager::class);

        $eventDispatcher   = $this->prophesize(DelayedEventDispatcher::class);
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(Events::PARTICIPANT_REMOVED, new ParticipantRemovedEvent($sheet))->shouldBeCalled();

        $meetingRepository      = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->countByParticipant($participant1)->shouldBeCalled()->willReturn(2);
        $meetingRepository->countByParticipant($participant2)->shouldBeCalled()->willReturn(0);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant1, $locale)->shouldBeCalled()->willReturn('jean paul');

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant1);
        $expectedSheet->addParticipant($participant2);
        $expectedSheet->addParticipant($participant3);

        $cartManager->updateParticipantsQuantity($sheet)->shouldBeCalled();

        // Command
        $remove = new Remove($sheet, $locale);
        $remove->participants = [
            $participant1,
            $participant2
        ];

        // Handle
        $handler = new RemoveHandler(
            $participantRepository->reveal(),
            $cartManager->reveal(),
            $eventDispatcher->reveal(),
            $meetingRepository->reveal(),
            $participantInfoGuesser->reveal()
        );
        $result         = $handler->handle($remove);
        $expectedResult = new RemoveResult(['jean paul']);

        $this->assertEquals($expectedSheet->countParticipants(), $sheet->countParticipants());
        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleException()
    {
        $this->expectException(CanNotRemoveAllParticipantsException::class);

        // Required
        $locale = 'fr';
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $owner = new User('email@email.fr', 'password', 'salt', 'fr');
        $user2 = new User('user@email.fr', 'password', 'salt', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $owner, $date);
        $participant1 = new Participant($sheet, $owner, [], true);
        $participant2 = new Participant($sheet, $user2, [], true);
        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $cartManager = $this->prophesize(CartManager::class);
        $eventDispatcher   = $this->prophesize(DelayedEventDispatcher::class);
        $eventDispatcher->dispatch(Events::PARTICIPANT_REMOVED, new ParticipantRemovedEvent($sheet))->shouldNotBeCalled();
        $meetingRepository      = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->countByParticipant($participant1)->shouldNotBeCalled();
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant1, $locale)->shouldNotBeCalled();

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant1);
        $expectedSheet->addParticipant($participant2);

        // Command
        $remove = new Remove($sheet, $locale);
        $remove->participants = [
            $participant1,
            $participant2,
        ];

        // Handle
        $handler = new RemoveHandler(
            $participantRepository->reveal(),
            $cartManager->reveal(),
            $eventDispatcher->reveal(),
            $meetingRepository->reveal(),
            $participantInfoGuesser->reveal()
        );
        $handler->handle($remove);

        $this->assertEquals($expectedSheet, $sheet);
    }
}
