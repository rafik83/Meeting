<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Package\Step;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Package\Step\SelectPlan;
use Proximum\Vimeet\Application\Command\Package\Step\SelectPlanHandler;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SelectPlanHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $datetime = new \DateTime();
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);
        $product  = Product::createPlan($event, 'plan', '', 100, 10, 40);

        $emptyCart    = new Cart($sheet, [], 1);
        $expectedCart = new Cart($sheet, [new CartRow($sheet, $product, 1)], 1);

        // Mock
        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet, 1)->shouldBeCalled()->willReturn($emptyCart);
        $cartManager->deleteCartStep($emptyCart)->shouldBeCalled();
        $cartManager->save($expectedCart)->shouldBeCalled();

        $plans        = new SelectPlan($sheet, 1);
        $plans->plan  = $product;

        $plansHandler = new SelectPlanHandler($cartManager->reveal());
        $plansHandler->handle($plans);
    }

    public function testHandleWithExistingCartRow()
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $datetime = new \DateTime();
        $product1 = Product::createPlan($event, 'plan1', '', 100, 10, 40);
        $product2 = Product::createPlan($event, 'plan2', '', 50, 10, 50);
        $user     = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);

        $actualCart   = new Cart($sheet, [new CartRow($sheet, $product1, 1)], 1);
        $expectedCart = new Cart($sheet, [new CartRow($sheet, $product2, 1)], 1);

        // Mock
        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet, 1)->shouldBeCalled()->willReturn($actualCart);
        $cartManager->deleteCartStep($actualCart)->shouldBeCalled();
        $cartManager->save(Argument::that(function (Cart $cart) use ($expectedCart) {
            $this->assertEquals($expectedCart->getSheet(), $cart->getSheet());
            $this->assertEquals($expectedCart->getRows(), $cart->getRows());

            return true;
        }))->shouldBeCalled();

        $plans        = new SelectPlan($sheet, 1);
        $plans->plan  = $product2;

        $plansHandler = new SelectPlanHandler($cartManager->reveal());
        $plansHandler->handle($plans);
    }
}
