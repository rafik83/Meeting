<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class CartTest extends \PHPUnit_Framework_TestCase
{
    public function testAddOneParticipantToCart()
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

        $expectedPlanCartRow = new CartRow($sheet, $planProduct, 1, $datetime);
        $expectedParticipantCartRow = new CartRow($sheet, $participantProduct, 1, $datetime);

        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->findCartRowParticipantBySheet($sheet)->shouldBeCalled()->willReturn(null);
        $cartRowRepository->findCartRowPlanBySheet($sheet)->shouldBeCalled()->willReturn($expectedPlanCartRow);
        $cartRowRepository->add($expectedParticipantCartRow)->shouldBeCalled();

        $cart = new Cart($cartRowRepository->reveal(), $datetime);
        $cart->addSheetParticipantsToCart($sheet);
    }

    public function testAddSeveralParticipantsToCart()
    {
        $event       = new Event();
        $type        = new Type($event);
        $datetime    = new \DateTime();
        $sheet       = new Sheet($event, $type, [], [], $datetime);

        $user1        = new User('john.doe@example.net', '_salt_', '_password_', 'fr');
        $participant1 = new Participant($sheet, $user1, [], true, true);
        $sheet->addParticipant($participant1);

        $user2        = new User('marge.doe@example.net', '_salt_', '_password_', 'fr');
        $participant2 = new Participant($sheet, $user2, [], false, true);
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

        $expectedPlanCartRow = new CartRow($sheet, $planProduct, 1, $datetime);
        $previousParticipantCartRow = new CartRow($sheet, $participantProduct, 1, $datetime);
        $expectedParticipantCartRow = new CartRow($sheet, $participantProduct, 2, $datetime);

        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->findCartRowPlanBySheet($sheet)->shouldBeCalled()->willReturn($expectedPlanCartRow);

        // There is previous participant added to cart
        $cartRowRepository->findCartRowParticipantBySheet($sheet)->shouldBeCalled()->willReturn($previousParticipantCartRow);

        // Delete it
        $cartRowRepository->delete($previousParticipantCartRow)->shouldBeCalled();

        // Add the new one with two participants
        $cartRowRepository->add($expectedParticipantCartRow)->shouldBeCalled();

        $cart = new Cart($cartRowRepository->reveal(), $datetime);
        $cart->addSheetParticipantsToCart($sheet);
    }
}
