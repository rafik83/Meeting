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
use Proximum\Vimeet\Application\Query\Package\Summary\GroupsViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\PromotionCodeQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\PromotionCodeQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\SummaryViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\SummaryViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\GroupsView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodesView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodeView;
use Proximum\Vimeet\Application\View\Package\Summary\SummaryView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use PHPUnit\Framework\TestCase;

class SummaryViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $datetime);
        $package->enable(true, true, true);
        $sheet   = SheetFactory::create($event, null, $datetime, $type);
        $product = ProductFactory::create($event, 'option');
        $funnel  = new Funnel($sheet);

        $package->setPlanning($product);
        $type->setPackage($package);

        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);

        // Mock
        $groupsViewQueryHandler    = $this->prophesize(GroupsViewQueryHandler::class);
        $vatApplicable             = $this->prophesize(VatApplicable::class);
        $promotionCodeQueryHandler = $this->prophesize(PromotionCodeQueryHandler::class);

        $groupsView         = new GroupsView();
        $promotionCodesView = new PromotionCodesView([new PromotionCodeView(1, '', '', 0, 'EUR', '', [])]);

        $groupsViewQueryHandler->handle(Argument::type(GroupsViewQuery::class))->shouldBeCalled()
            ->willReturn($groupsView);

        $promotionCodeQueryHandler->handle(Argument::type(PromotionCodeQuery::class))->shouldBeCalled()
            ->willReturn($promotionCodesView);

        $vatApplicable->onSheet($sheet)->shouldBeCalled()->willReturn(true);

        $handler = new SummaryViewQueryHandler(
            $groupsViewQueryHandler->reveal(),
            $vatApplicable->reveal(),
            $promotionCodeQueryHandler->reveal()
        );

        // Expected
        $expectedSummaryView = new SummaryView(
            $funnel,
            $groupsView,
            $promotionCodesView,
            $event->getMode(),
            $event->getVat(),
            0,
            $event->getCurrency(),
            true
        );

        $query = new SummaryViewQuery($sheet, $funnel, $cart, $locale);

        $summaryView = $handler->handle($query);

        $this->assertEquals($expectedSummaryView, $summaryView);
    }
}
