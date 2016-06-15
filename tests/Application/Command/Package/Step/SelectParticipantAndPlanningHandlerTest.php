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
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

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

        $expectedPlanningCartRow = new CartRow($sheet, $planningProduct, 1, $datetime);

        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);

        $cartRowRepository->findCartRowPlanningBySheet($sheet)->shouldBeCalled()->willReturn(null);
        $cartRowRepository->add($expectedPlanningCartRow)->shouldBeCalled();

        $selectParticipantAndPlanning = new SelectParticipantAndPlanning($sheet);
        $selectParticipantAndPlanning->planningQuantity = 1;

        $selectParticipantAndPlanningHandler = new SelectParticipantAndPlanningHandler(
            $cartRowRepository->reveal(), $this->prophesize(Cart::class)->reveal(), $datetime
        );
        $selectParticipantAndPlanningHandler->handle($selectParticipantAndPlanning);
    }
}
