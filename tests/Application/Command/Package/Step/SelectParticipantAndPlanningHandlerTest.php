<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanningHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class SelectParticipantAndPlanningHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $type        = new Type($event);
        $datetime    = new \DateTime();
        $user        = new User('john.doe@example.net', '_salt_', '_password_', 'fr');
        $sheet       = new Sheet($event, $type, [], $user, $datetime);
        $participant = new Participant($sheet, $user, [], true, true);
        $sheet->addParticipant($participant);

        $planProduct        = Product::createPlan($event, 'plan', '', 100, 10, 40);
        $planningProduct    = Product::createPlanning($event, 'planning', 50, 10);
        $participantProduct = Product::createParticipant($event, 'participant', 50, 10);

        $package  = new Package($event, 'My package', $datetime);
        $package->enable(true, true, true);
        $package->setPlans([$planProduct]);
        $package->setPlanning($planningProduct);
        $package->setParticipants([$participantProduct]);
        $type->setPackage($package);

        $promotionCode    = new PromotionCode($event, 'My promotion code', 'AXYZ', 1, $datetime->modify('+1 month'));
        $planRow          = new CartRow($sheet, $planProduct, 1);
        $promotionCodeRow = new PromotionCodeRow($sheet, $promotionCode);

        $actualCart   = new Cart($sheet, [$planRow], [$promotionCodeRow], 1);
        $expectedCart = new Cart(
            $sheet,
            [
                $planRow,
                new CartRow($sheet, $participantProduct, 1),
                new CartRow($sheet, $planningProduct, 1),
            ],
            [$promotionCodeRow],
            1
            );

        $cartManager = $this->prophesize(CartManager::class);
        $orderMerger = $this->prophesize(Merger::class);
        $cartManager->getCart($sheet, 1)->shouldBeCalled()->willReturn($actualCart);
        $cartManager->save($expectedCart)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $packageStepDone = new StepDoneEvent($sheet);
        $eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone)->shouldBeCalled();

        $command                   = new SelectParticipantAndPlanning($sheet, 1);
        $command->planningQuantity = 1;

        $handler = new SelectParticipantAndPlanningHandler(
            $cartManager->reveal(),
            $orderMerger->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }

    public function testWithSeveralParticipantsAndNoPlanningHandle()
    {
        $event       = EventFactory::createEvent();
        $type        = new Type($event);
        $datetime    = new \DateTime();

        $user1        = new User('john.doe@example.net', '_salt_', '_password_', 'fr');
        $sheet        = new Sheet($event, $type, [], $user1, $datetime);
        $participant1 = new Participant($sheet, $user1, [], true, true);

        $sheet->addParticipant($participant1);

        $user2        = new User('clara.doe@example.net', '_salt_', '_password_', 'fr');
        $participant2 = new Participant($sheet, $user2, [], true, true);
        $sheet->addParticipant($participant2);

        $planProduct        = Product::createPlan($event, 'plan', '', 100, 10, 40);
        $planningProduct    = Product::createPlanning($event, 'planning', 50, 10);
        $participantProduct = Product::createParticipant($event, 'participant', 50, 10);

        $package  = new Package($event, 'My package', $datetime);
        $package->enable(true, true, true);
        $package->setPlans([$planProduct]);
        $package->setPlanning($planningProduct);
        $package->setParticipants([$participantProduct]);
        $type->setPackage($package);

        $promotionCode    = new PromotionCode($event, 'My promotion code', 'AXYZ', 1, $datetime->modify('+1 month'));
        $planRow = new CartRow($sheet, $planProduct, 1);
        $promotionCodeRow = new PromotionCodeRow($sheet, $promotionCode);

        $actualCart = new Cart(
            $sheet,
            [
                $planRow,
                new CartRow($sheet, $participantProduct, 1),
            ],
            [$promotionCodeRow],
            1
        );

        $expectedCart = new Cart(
            $sheet,
            [
                $planRow,
                new CartRow($sheet, $participantProduct, 2),
            ],
            [$promotionCodeRow],
            1);

        $cartManager = $this->prophesize(CartManager::class);
        $orderMerger = $this->prophesize(Merger::class);
        $cartManager->getCart($sheet, 1)->shouldBeCalled()->willReturn($actualCart);
        $cartManager->save($expectedCart)->shouldBeCalled();
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $packageStepDone = new StepDoneEvent($sheet);
        $eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone)->shouldBeCalled();

        $command                   = new SelectParticipantAndPlanning($sheet, 1);
        $command->planningQuantity = 0;

        $handler = new SelectParticipantAndPlanningHandler(
            $cartManager->reveal(),
            $orderMerger->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }
}
