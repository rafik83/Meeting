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
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Application\Command\Participant\Remove;
use Proximum\Vimeet\Application\Command\Participant\RemoveHandler;
use Proximum\Vimeet\Application\Command\Participant\RemoveResult;
use Proximum\Vimeet\Application\Components\Step\StepParticipantAndPlanning;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
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
    public function testHandle(): void
    {
        // Required
        $locale = 'fr';
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $owner = new User('email@email.fr', 'password', 'salt', 'fr');
        $date = new \DateTime();
        $sheet = new Sheet($event, $type, [], $owner, $date);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(1999);
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(1337);
        $participant1->getUser()->willReturn($user->reveal());
        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->willReturn(2059);
        $sheet->addParticipant($participant1->reveal());
        $sheet->addParticipant($participant2->reveal());

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $cartManager = $this->prophesize(CartManager::class);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent)->shouldBeCalled();
        $eventDispatcher
            ->dispatch(Events::PARTICIPANT_REMOVED, new ParticipantRemovedEvent($sheet, [1999 => $user->reveal()]))
            ->shouldBeCalled()
        ;

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->hasScheduledMeetingByParticipant($participant1->reveal())->shouldBeCalled()->willReturn(false);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant1->reveal(), $locale)->shouldNotBeCalled();

        $stepParticipantAndPlanning = $this->prophesize(StepParticipantAndPlanning::class);

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant2->reveal());

        $product = $this->prophesize(Product::class);
        $selectParticipantAndPlanning = new SelectParticipantAndPlanning($sheet);
        $selectParticipantAndPlanning->participantsProduct = [2059 => $product->reveal()];
        $stepParticipantAndPlanning->build($sheet)->shouldBeCalled()->willReturn($selectParticipantAndPlanning);

        $cart = $this->prophesize(Cart::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart->reveal());
        $products = [2059 => $product->reveal()];
        $cartManager
            ->updateParticipantsQuantity($cart->reveal(), $products)
            ->shouldBeCalled()
            ->willReturn($cart->reveal())
        ;
        $cartManager->save($cart)->shouldBeCalled();

        // Command
        $remove = new Remove($sheet, $locale);
        $remove->participants = [$participant1->reveal()];

        // Handle
        $handler = new RemoveHandler(
            $participantRepository->reveal(),
            $cartManager->reveal(),
            $eventDispatcher->reveal(),
            $meetingRepository->reveal(),
            $participantInfoGuesser->reveal(),
            $stepParticipantAndPlanning->reveal()
        );
        $result = $handler->handle($remove);
        $expectedResult = new RemoveResult();

        $this->assertEquals($expectedSheet->countParticipants(), $sheet->countParticipants());
        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleWithMeeting(): void
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
        $this->setIdToParticipantMock($participant1, 11);
        $this->setIdToParticipantMock($participant2, 12);
        $this->setIdToParticipantMock($participant3, 13);
        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);
        $sheet->addParticipant($participant3);

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $cartManager = $this->prophesize(CartManager::class);

        $eventDispatcher   = $this->prophesize(DelayedEventDispatcher::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $meetingRepository      = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->hasScheduledMeetingByParticipant($participant1)->shouldBeCalled()->willReturn(true);
        $meetingRepository->hasScheduledMeetingByParticipant($participant2)->shouldBeCalled()->willReturn(false);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant1, $locale)->shouldBeCalled()->willReturn('jean paul');

        $stepParticipantAndPlanning = $this->prophesize(StepParticipantAndPlanning::class);

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant1);
        $expectedSheet->addParticipant($participant2);
        $expectedSheet->addParticipant($participant3);

        $stepParticipantAndPlanning->build(Argument::any())->shouldNotBeCalled();

        $cartManager->getCart($sheet)->shouldNotBeCalled();
        $cartManager->updateParticipantsQuantity(Argument::any())->shouldNotBeCalled();
        $cartManager->save(Argument::any())->shouldNotBeCalled();

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
            $participantInfoGuesser->reveal(),
            $stepParticipantAndPlanning->reveal()
        );
        $result         = $handler->handle($remove);
        $expectedResult = new RemoveResult([11 => 'jean paul'], true);

        $this->assertEquals($expectedSheet->countParticipants(), $sheet->countParticipants());
        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleException(): void
    {
        $this->expectException(CanNotRemoveAllParticipantsException::class);

        // Required
        $locale = 'fr';
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $owner = new User('email@email.fr', 'password', 'salt', 'fr');
        $date = new \DateTime();
        $sheet = new Sheet($event, $type, [], $owner, $date);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $sheet->addParticipant($participant1->reveal());
        $sheet->addParticipant($participant2->reveal());

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $cartManager = $this->prophesize(CartManager::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->hasScheduledMeetingByParticipant($participant1->reveal())->shouldNotBeCalled();
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant1->reveal(), $locale)->shouldNotBeCalled();
        $stepParticipantAndPlanning = $this->prophesize(StepParticipantAndPlanning::class);

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant1->reveal());
        $expectedSheet->addParticipant($participant2->reveal());

        // Command
        $remove = new Remove($sheet, $locale);
        $remove->participants = [
            $participant1->reveal(),
            $participant2->reveal(),
        ];

        // Handle
        $handler = new RemoveHandler(
            $participantRepository->reveal(),
            $cartManager->reveal(),
            $eventDispatcher->reveal(),
            $meetingRepository->reveal(),
            $participantInfoGuesser->reveal(),
            $stepParticipantAndPlanning->reveal()
        );
        $handler->handle($remove);

        $this->assertEquals($expectedSheet, $sheet);
    }

    /**
     * @param Participant $participant
     * @param int         $id
     */
    private function setIdToParticipantMock(Participant $participant, $id): void
    {
        $reflection  = new \ReflectionClass(Participant::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);
        $property->setAccessible(false);
    }
}
