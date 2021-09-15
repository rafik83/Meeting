<?php

namespace Proximum\Vimeet\Tests\Application\Command\Package\Step;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanningHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SelectParticipantAndPlanningHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);

        $planProduct = Product::createPlan($event->reveal(), 'plan', '', 100, 20, 10, 40);
        $planningProduct = Product::createPlanning($event->reveal(), 'planning', 50, 20, 10);
        $participantProduct = Product::createParticipant($event->reveal(), 'participant', 50, 20, 10);

        $package = new Package($event->reveal(), 'My package', $dateTime);
        $package->enable(true, true, true);
        $package->setPlans([$planProduct]);
        $package->setPlanning($planningProduct);
        $package->setParticipants([$participantProduct]);

        $participant = $this->prophesize(Participant::class);
        $participant->getId()->willReturn(123);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getNotCancelledOrders()->willReturn([]);
        $sheet->getParticipantsArray()->willReturn([$participant->reveal()]);
        $sheet->getPackage()->willReturn($package);

        $promotionCode = new PromotionCode(
            $event->reveal(),
            'My promotion code',
            'AXYZ',
            1,
            $dateTime->modify('+1 month')
        );
        $planRow = new CartRow($sheet->reveal(), $planProduct, 1);
        $promotionCodeRow = new PromotionCodeRow($sheet->reveal(), $promotionCode);

        $actualCart = new Cart($sheet->reveal(), [$planRow], [$promotionCodeRow], 1);

        $participantProductRow = new CartRow($sheet->reveal(), $participantProduct, 1);
        $participantProductRow->addCartRowParticipant(
            new CartRowParticipant($participantProductRow, $participant->reveal())
        );

        $expectedCart = new Cart(
            $sheet->reveal(),
            [
                $planRow,
                $participantProductRow,
                new CartRow($sheet->reveal(), $planningProduct, 1),
            ],
            [$promotionCodeRow],
            1
        );

        $cartManager = $this->prophesize(CartManager::class);
        $orderMerger = $this->prophesize(Merger::class);
        $cartManager->getCart($sheet->reveal(), 1)->shouldBeCalled()->willReturn($actualCart);
        $productsByParticipantId = [123 => $participantProduct];
        $cartManager
            ->updateParticipantsQuantity($actualCart, $productsByParticipantId)
            ->shouldBeCalled()
            ->willReturn(new Cart(
                $sheet->reveal(),
                [
                    $planRow,
                    $participantProductRow,
                ],
                [$promotionCodeRow],
                1
            ))
        ;

        $cartManager
            ->save($expectedCart)
            ->shouldBeCalled()
        ;

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $packageStepDone = new StepDoneEvent($sheet->reveal(), 'participant_planning');
        $eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone)->shouldBeCalled();

        $command = new SelectParticipantAndPlanning($sheet->reveal(), 1);
        $command->planningQuantity = new OptionRow(1);
        $command->participantsProduct[123] = $participantProduct;

        $handler = new SelectParticipantAndPlanningHandler(
            $cartManager->reveal(),
            $orderMerger->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }

    public function testWithSeveralParticipantsAndNoPlanningHandle()
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);

        $planProduct = Product::createPlan($event->reveal(), 'plan', '', 100, 20, 10, 40);
        $planningProduct = Product::createPlanning($event->reveal(), 'planning', 50, 20, 10);

        $participantProduct1 = $this->prophesize(Product::class);
        $participantProduct1->getId()->willReturn(1111);
        $participantProduct1->isParticipant()->willReturn(true);
        $participantProduct1->getSerializedData()->willReturn('participantProductSerializedData1');

        $participantProduct2 = $this->prophesize(Product::class);
        $participantProduct2->getId()->willReturn(2222);
        $participantProduct2->isParticipant()->willReturn(true);
        $participantProduct2->getSerializedData()->willReturn('participantProductSerializedData2');

        $package = new Package($event->reveal(), 'My package', $dateTime);
        $package->enable(true, true, true);
        $package->setPlans([$planProduct]);
        $package->setPlanning($planningProduct);
        $package->setParticipants([$participantProduct1->reveal(), $participantProduct2->reveal()]);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(11);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->willReturn(22);

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getId()->willReturn(33);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getNotCancelledOrders()->willReturn([]);
        $sheet->getParticipantsArray()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
                $participant3->reveal(),
            ]
        );
        $sheet->getPackage()->willReturn($package);

        $planRow = new CartRow($sheet->reveal(), $planProduct, 1);
        $actualCart = new Cart($sheet->reveal(), [$planRow], [], 1);

        $participantProductRow1 = new CartRow($sheet->reveal(), $participantProduct1->reveal(), 2);
        $participantProductRow1->addCartRowParticipant(
            new CartRowParticipant($participantProductRow1, $participant1->reveal())
        );
        $participantProductRow1->addCartRowParticipant(
            new CartRowParticipant($participantProductRow1, $participant2->reveal())
        );

        $participantProductRow2 = new CartRow($sheet->reveal(), $participantProduct2->reveal(), 1);
        $participantProductRow2->addCartRowParticipant(
            new CartRowParticipant($participantProductRow2, $participant3->reveal())
        );

        $expectedCart = new Cart(
            $sheet->reveal(),
            [
                $planRow,
                $participantProductRow1,
                $participantProductRow2,
            ],
            [],
            1
        );

        $cartManager = $this->prophesize(CartManager::class);
        $orderMerger = $this->prophesize(Merger::class);
        $cartManager->getCart($sheet->reveal(), 1)->shouldBeCalled()->willReturn($actualCart);

        $productsByParticipantId = [
            11 => $participantProduct1->reveal(),
            22 => $participantProduct1->reveal(),
            33 => $participantProduct2->reveal(),
        ];

        $cartManager
            ->updateParticipantsQuantity($actualCart, $productsByParticipantId)
            ->shouldBeCalled()
            ->willReturn($expectedCart)
        ;
        $cartManager->save($expectedCart)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $packageStepDone = new StepDoneEvent($sheet->reveal(), 'participant_planning');
        $eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone)->shouldBeCalled();

        $command = new SelectParticipantAndPlanning($sheet->reveal(), 1);
        $command->planningQuantity = new OptionRow(0);
        // Participants 1 and 2 have $participantProduct1
        $command->participantsProduct[11] = $participantProduct1->reveal();
        $command->participantsProduct[22] = $participantProduct1->reveal();
        // Participants 3 have $participantProduct2
        $command->participantsProduct[33] = $participantProduct2->reveal();

        $handler = new SelectParticipantAndPlanningHandler(
            $cartManager->reveal(),
            $orderMerger->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }
}
