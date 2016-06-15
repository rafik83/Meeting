<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Command\Package\Step\SelectPlan;
use Proximum\Vimeet\Application\Command\Package\Step\SelectPlanHandler;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class SelectPlanHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event    = new Event();
        $type     = new Type($event);
        $datetime = new \DateTime();
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);
        $product  = Product::createPlan(
            $event,
            'plan',
            '',
            100,
            10,
            40
        );

        // Expected
        $cartRow = new CartRow(
            $sheet,
            $product,
            1,
            $datetime
        );

        // Mock
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->findCartRowPlanBySheet($sheet)->shouldBeCalled()->willReturn(null);
        $cartRowRepository->add($cartRow)->shouldBeCalled();
        $dateTime          = new \DateTimeImmutable();

        $plans        = new SelectPlan($sheet);
        $plans->plan  = $product;
        $plans->sheet = $sheet;

        $plansHandler = new SelectPlanHandler($cartRowRepository->reveal(), $dateTime);
        $plansHandler->handle($plans);
    }

    public function testHandleWithExistingCartRow()
    {
        $event    = new Event();
        $type     = new Type($event);
        $datetime = new \DateTime();
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);
        $product  = Product::createPlan(
            $event,
            'plan',
            '',
            100,
            10,
            40
        );
        $product2 = Product::createPlan(
            $event,
            'plan',
            '',
            50,
            10,
            50
        );

        $cartRow = new CartRow(
            $sheet,
            $product2,
            1,
            $datetime
        );

        // Expected
        $expectedCartRow = new CartRow(
            $sheet,
            $product,
            1,
            $datetime
        );

        // Mock
        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $dateTime          = new \DateTimeImmutable();
        $cartRowRepository->findCartRowPlanBySheet($sheet)->shouldBeCalled()->willReturn($cartRow);
        $cartRowRepository->delete($cartRow)->shouldBeCalled();
        $cartRowRepository->add($expectedCartRow)->shouldBeCalled();

        $plans        = new SelectPlan($sheet);
        $plans->plan  = $product;
        $plans->sheet = $sheet;

        $plansHandler = new SelectPlanHandler($cartRowRepository->reveal(), $dateTime);
        $plansHandler->handle($plans);
    }
}
