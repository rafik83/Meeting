<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Application\Command\Participant\Remove;
use Proximum\Vimeet\Application\Command\Participant\RemoveHandler;
use Proximum\Vimeet\Application\Components\Participant\Remove\ConflictsView;
use Proximum\Vimeet\Application\Components\Participant\Remove\ParticipantConflictView;
use Proximum\Vimeet\Application\Components\Participant\Remove\ProductAttributedToParticipantConflictChecker;
use Proximum\Vimeet\Application\Components\Step\StepParticipantAndPlanning;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Application\Exception\Participant\Remove\ParticipantAttributedToProductCanNotBeRemovedException;
use Proximum\Vimeet\Application\Exception\Participant\Remove\ParticipantWithMeetingCanNotBeRemovedException;
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
    /** @var ObjectProphecy */
    private $participantRepository;

    /** @var ObjectProphecy */
    private $cartManager;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $participantInfoGuesser;

    /** @var ObjectProphecy */
    private $stepParticipantAndPlanning;

    /** @var ObjectProphecy */
    private $productAttributedToParticipantConflictChecker;

    public function setUp()
    {
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->cartManager = $this->prophesize(CartManager::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->stepParticipantAndPlanning = $this->prophesize(StepParticipantAndPlanning::class);
        $this->productAttributedToParticipantConflictChecker = $this->prophesize(ProductAttributedToParticipantConflictChecker::class);
    }

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
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent)->shouldBeCalled();
        $this->eventDispatcher
            ->dispatch(Events::PARTICIPANT_REMOVED, new ParticipantRemovedEvent($sheet, [1999 => $user->reveal()]))
            ->shouldBeCalled()
        ;
        $this->meetingRepository->hasScheduledMeetingByParticipant($participant1->reveal())->shouldBeCalled()->willReturn(false);
        $this->participantInfoGuesser->guessParticipantCompleteName($participant1->reveal(), $locale)->shouldNotBeCalled();

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant2->reveal());

        $product = $this->prophesize(Product::class);
        $selectParticipantAndPlanning = new SelectParticipantAndPlanning($sheet);
        $selectParticipantAndPlanning->participantsProduct = [2059 => $product->reveal()];
        $this->stepParticipantAndPlanning->build($sheet)->shouldBeCalled()->willReturn($selectParticipantAndPlanning);

        $cart = $this->prophesize(Cart::class);
        $this->cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart->reveal());
        $products = [2059 => $product->reveal()];
        $this->cartManager
            ->updateParticipantsQuantity($cart->reveal(), $products)
            ->shouldBeCalled()
            ->willReturn($cart->reveal())
        ;
        $this->cartManager->save($cart)->shouldBeCalled();

        $this->productAttributedToParticipantConflictChecker
            ->getParticipantsWithConflictOnProductAttributed([$participant1->reveal()], $locale)
            ->shouldBeCalled()
            ->willReturn(new ConflictsView())
        ;

        // Command
        $remove = new Remove($sheet, $locale);
        $remove->participants = [$participant1->reveal()];

        // Handle
        $handler = new RemoveHandler(
            $this->participantRepository->reveal(),
            $this->cartManager->reveal(),
            $this->eventDispatcher->reveal(),
            $this->meetingRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->stepParticipantAndPlanning->reveal(),
            $this->productAttributedToParticipantConflictChecker->reveal()
        );
        $handler->handle($remove);

        $this->assertEquals($expectedSheet->countParticipants(), $sheet->countParticipants());
    }

    public function testHandleWithMeeting(): void
    {
        $this->expectException(ParticipantWithMeetingCanNotBeRemovedException::class);

        // Required
        $locale = 'fr';
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $owner = new User('email@email.fr', 'password', 'salt', 'fr');
        $user2 = new User('user@email.fr', 'password', 'salt', 'fr');
        $user3 = new User('user3@email.fr', 'password', 'salt', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $owner, $date);
        $participant1 = new Participant($sheet, $owner, [], true, $date);
        $participant2 = new Participant($sheet, $user2, [], true, $date);
        $participant3 = new Participant($sheet, $user3, [], true, $date);
        $this->setIdToParticipantMock($participant1, 11);
        $this->setIdToParticipantMock($participant2, 12);
        $this->setIdToParticipantMock($participant3, 13);
        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);
        $sheet->addParticipant($participant3);

        // Mock
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->meetingRepository->hasScheduledMeetingByParticipant($participant1)->shouldBeCalled()->willReturn(true);
        $this->meetingRepository->hasScheduledMeetingByParticipant($participant2)->shouldBeCalled()->willReturn(false);
        $this->participantInfoGuesser->guessParticipantCompleteName($participant1, $locale)->shouldBeCalled()->willReturn('jean paul');

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant1);
        $expectedSheet->addParticipant($participant2);
        $expectedSheet->addParticipant($participant3);

        $this->stepParticipantAndPlanning->build(Argument::any())->shouldNotBeCalled();

        $this->cartManager->getCart($sheet)->shouldNotBeCalled();
        $this->cartManager->updateParticipantsQuantity(Argument::any())->shouldNotBeCalled();
        $this->cartManager->save(Argument::any())->shouldNotBeCalled();

        $this->productAttributedToParticipantConflictChecker
            ->getParticipantsWithConflictOnProductAttributed(Argument::any())
            ->shouldNotBeCalled()
        ;

        // Command
        $remove = new Remove($sheet, $locale);
        $remove->participants = [
            $participant1,
            $participant2,
        ];

        // Handle
        $handler = new RemoveHandler(
            $this->participantRepository->reveal(),
            $this->cartManager->reveal(),
            $this->eventDispatcher->reveal(),
            $this->meetingRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->stepParticipantAndPlanning->reveal(),
            $this->productAttributedToParticipantConflictChecker->reveal()
        );
        $handler->handle($remove);

        $this->assertEquals($expectedSheet->countParticipants(), $sheet->countParticipants());


        $exception = $this->getExpectedException();
        $this->assertEquals([11 => 'jean paul'], $exception->getParticipantNames());
    }

    public function testHandleWithProductAttributedConflict(): void
    {
        $this->expectException(ParticipantAttributedToProductCanNotBeRemovedException::class);

        // Required
        $locale = 'fr';
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $owner = new User('email@email.fr', 'password', 'salt', 'fr');
        $user2 = new User('user@email.fr', 'password', 'salt', 'fr');
        $user3 = new User('user3@email.fr', 'password', 'salt', 'fr');
        $date  = new \DateTime();
        $sheet = new Sheet($event, $type, [], $owner, $date);
        $participant1 = new Participant($sheet, $owner, [], true, $date);
        $participant2 = new Participant($sheet, $user2, [], true, $date);
        $participant3 = new Participant($sheet, $user3, [], true, $date);
        $this->setIdToParticipantMock($participant1, 11);
        $this->setIdToParticipantMock($participant2, 12);
        $this->setIdToParticipantMock($participant3, 13);
        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);
        $sheet->addParticipant($participant3);

        // Mock
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->meetingRepository->hasScheduledMeetingByParticipant($participant1)->shouldBeCalled()->willReturn(false);
        $this->meetingRepository->hasScheduledMeetingByParticipant($participant2)->shouldBeCalled()->willReturn(false);
        $this->participantInfoGuesser->guessParticipantCompleteName(Argument::any())->shouldNotBeCalled();

        // Expected
        $expectedSheet = new Sheet($event, $type, [], $owner, $date);
        $expectedSheet->addParticipant($participant1);
        $expectedSheet->addParticipant($participant2);
        $expectedSheet->addParticipant($participant3);

        $this->stepParticipantAndPlanning->build(Argument::any())->shouldNotBeCalled();

        $this->cartManager->getCart($sheet)->shouldNotBeCalled();
        $this->cartManager->updateParticipantsQuantity(Argument::any())->shouldNotBeCalled();
        $this->cartManager->save(Argument::any())->shouldNotBeCalled();

        $conflictView = new ConflictsView();
        $conflictView->addConflict(new ParticipantConflictView(12, 'Jean Michel'));
        $this->productAttributedToParticipantConflictChecker
            ->getParticipantsWithConflictOnProductAttributed([$participant1, $participant2], $locale)
            ->shouldBeCalled()
            ->willReturn($conflictView)
        ;

        // Command
        $remove = new Remove($sheet, $locale);
        $remove->participants = [
            $participant1,
            $participant2,
        ];

        // Handle
        $handler = new RemoveHandler(
            $this->participantRepository->reveal(),
            $this->cartManager->reveal(),
            $this->eventDispatcher->reveal(),
            $this->meetingRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->stepParticipantAndPlanning->reveal(),
            $this->productAttributedToParticipantConflictChecker->reveal()
        );
        $handler->handle($remove);

        $this->assertEquals($expectedSheet->countParticipants(), $sheet->countParticipants());


        $exception = $this->getExpectedException();
        $this->assertEquals([12 => 'Jean Michel'], $exception->getParticipantNames());
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
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->meetingRepository->hasScheduledMeetingByParticipant($participant1->reveal())->shouldNotBeCalled();
        $this->participantInfoGuesser->guessParticipantCompleteName($participant1->reveal(), $locale)->shouldNotBeCalled();

        $this->productAttributedToParticipantConflictChecker
            ->getParticipantsWithConflictOnProductAttributed(Argument::any())
            ->shouldNotBeCalled()
        ;

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
            $this->participantRepository->reveal(),
            $this->cartManager->reveal(),
            $this->eventDispatcher->reveal(),
            $this->meetingRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->stepParticipantAndPlanning->reveal(),
            $this->productAttributedToParticipantConflictChecker->reveal()
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
