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
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Cart\CartManager;

class SelectParticipantAndPlanningHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = new Event();
        $type        = new Type($event);
        $datetime    = new \DateTime();
        $sheet       = new Sheet($event, $type, [], [], $datetime);
        $user        = new User('john.doe@example.net', '_salt_', '_password_', 'fr');
        $participant = new Participant($sheet, $user, [], true, true);
        $sheet->addParticipant($participant);

        $planProduct        = Product::createPlan($event, 'plan', '', 100, 10, 40);
        $planningProduct    = Product::createPlanning($event, 'planning', 50, 10);
        $participantProduct = Product::createParticipant($event, 'participant', 50, 10);

        $package  = new Package($event, 'My package', $datetime);
        $package->enable(true, true, true);
        $package->setPlans([$planProduct]);
        $package->setPlanning($planningProduct);
        $package->setParticipant($participantProduct);
        $type->setPackage($package);

        $planRow = new CartRow($sheet, $planProduct, 1);

        $actualCart = new Cart($sheet, [$planRow]);
        $expectedCart = new Cart($sheet, [
            $planRow,
            new CartRow($sheet, $participantProduct, 1),
            new CartRow($sheet, $planningProduct, 1),
        ]);

        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($actualCart);
        $cartManager->save($expectedCart)->shouldBeCalled();

        $command                   = new SelectParticipantAndPlanning($sheet);
        $command->planningQuantity = 1;

        $handler = new SelectParticipantAndPlanningHandler($cartManager->reveal());
        $handler->handle($command);
    }

    public function testWithSeveralParticipantsAndNoPlanningHandle()
    {
        $event       = new Event();
        $type        = new Type($event);
        $datetime    = new \DateTime();
        $sheet       = new Sheet($event, $type, [], [], $datetime);

        $user1        = new User('john.doe@example.net', '_salt_', '_password_', 'fr');
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
        $package->setParticipant($participantProduct);
        $type->setPackage($package);

        $planRow = new CartRow($sheet, $planProduct, 1);

        $actualCart = new Cart($sheet, [
            $planRow,
            new CartRow($sheet, $participantProduct, 1)
        ]);

        $expectedCart = new Cart($sheet, [
            $planRow,
            new CartRow($sheet, $participantProduct, 2),
        ]);

        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($actualCart);
        $cartManager->save($expectedCart)->shouldBeCalled();

        $command                   = new SelectParticipantAndPlanning($sheet);
        $command->planningQuantity = 0;

        $handler = new SelectParticipantAndPlanningHandler($cartManager->reveal());
        $handler->handle($command);
    }
}
