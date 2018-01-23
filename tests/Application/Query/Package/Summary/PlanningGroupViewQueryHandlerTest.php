<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Package\Summary;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Package\Summary\PlanningGroupViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\PlanningGroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\PlanGroupView;
use Proximum\Vimeet\Application\View\Package\Summary\PlanningGroupView;
use Proximum\Vimeet\Application\View\Package\Summary\ProductView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use PHPUnit\Framework\TestCase;

class PlanningGroupViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $datetime);
        $package->enable(false, true, false);
        $sheet   = SheetFactory::create($event, null, $datetime, $type);
        $product = ProductFactory::create($event, 'planning');

        $package->setPlanning($product);
        $type->setPackage($package);

        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);

        $planGroupView = new PlanGroupView('label', [], 0.0);

        $productView = new ProductView(
            1,
            'Planning1',
            25,
            1, // quantity
            25, // total
            $event->getMode(),
            $event->getCurrency()
        );

        // Expected
        $expectedPlanningGroupView = new PlanningGroupView('', [$productView], 25);

        // Mock
        $productViewQueryHandler = $this->prophesize(ProductViewQueryHandler::class);

        $productViewQueryHandler->handle(Argument::that(function (ProductViewQuery $query) {
            return true;
        }))->shouldBeCalled()->willReturn($productView);

        $handler           = new PlanningGroupViewQueryHandler($productViewQueryHandler->reveal());
        $query             = new PlanningGroupViewQuery($sheet, $cart, $locale, $planGroupView);
        $planningGroupView = $handler->handle($query);

        $this->assertEquals($planningGroupView, $expectedPlanningGroupView);
    }

    public function testPlanningNotEnabledException()
    {
        $this->expectException(\Exception::class);

        $datetime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $datetime);
        $package->enable(false, false, false);
        $sheet   = SheetFactory::create($event, null, $datetime, $type);
        $product = ProductFactory::create($event, 'planning');

        $package->setPlanning($product);
        $type->setPackage($package);

        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);

        $planGroupView = new PlanGroupView('label', [], 0.0);

        // Mock
        $productViewQueryHandler = $this->prophesize(ProductViewQueryHandler::class);

        $productViewQueryHandler->handle(Argument::type(ProductViewQuery::class))->shouldNotBeCalled();

        $handler           = new PlanningGroupViewQueryHandler($productViewQueryHandler->reveal());
        $query             = new PlanningGroupViewQuery($sheet, $cart, $locale, $planGroupView);
        $handler->handle($query);
    }
}
