<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Participant\Remove;
use Proximum\Vimeet\Application\Command\Participant\RemoveHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Required
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


        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant2);

        $cartManager->updateParticipantsQuantity($sheet)->shouldBeCalled();

        // Command
        $remove = new Remove($sheet);
        $remove->participants = [
            $participant1
        ];

        // Handle
        $handler = new RemoveHandler(
            $participantRepository->reveal(),
            $cartManager->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($remove);

        $this->assertEquals($expectedSheet->countParticipants(), $sheet->countParticipants());
    }

    public function testHandleException()
    {
        $this->expectException(CanNotRemoveAllParticipantsException::class);

        // Required
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

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant1);
        $expectedSheet->addParticipant($participant2);

        // Command
        $remove = new Remove($sheet);
        $remove->participants = [
            $participant1,
            $participant2,
        ];

        // Handle
        $handler = new RemoveHandler(
            $participantRepository->reveal(),
            $cartManager->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($remove);

        $this->assertEquals($expectedSheet, $sheet);
    }
}
