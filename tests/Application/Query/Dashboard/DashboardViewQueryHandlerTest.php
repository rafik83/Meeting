<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Dashboard\DashboardSheetViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardSheetViewQueryHandler;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardTransactionViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardTransactionViewQueryHandler;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardViewQueryHandler;
use Proximum\Vimeet\Application\View\Dashboard\DashboardSheetView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardTransactionView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class DashboardViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $locale = 'fr';
        $query  = new DashboardViewQuery($event, $locale);

        //Expected
        $dashboardTransactionView = new DashboardTransactionView(100, 25, 75);
        $dashboardSheetView = new DashboardSheetView(100, 150, [], []);
        $dashboardViewExpected = new DashboardView($dashboardTransactionView, $dashboardSheetView);

        // Mock
        $dashboardTransactionHandler      = $this->prophesize(DashboardTransactionViewQueryHandler::class);
        $dashboardSheetHandler = $this->prophesize(DashboardSheetViewQueryHandler::class);

        $dashboardTransactionHandler->handle(
            new DashboardTransactionViewQuery($event)
        )->shouldBeCalled()->willReturn($dashboardTransactionView);

        $dashboardSheetHandler->handle(
            new DashboardSheetViewQuery($event, $locale)
        )->shouldBeCalled()->willReturn($dashboardSheetView);

        $handler = new DashboardViewQueryHandler(
            $dashboardTransactionHandler->reveal(),
            $dashboardSheetHandler->reveal()
        );

        $dashboardView = $handler->handle($query);

        $this->assertEquals($dashboardViewExpected, $dashboardView);
    }
}
