<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardContactViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardContactViewQueryHandler;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardMeetingViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardMeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardSheetViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardSheetViewQueryHandler;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardTransactionViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardTransactionViewQueryHandler;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardViewQueryHandler;
use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardContactView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardMeetingView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardSheetView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardTransactionView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DashboardViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $locale = 'fr';
        $query = new DashboardViewQuery($event, $locale);

        //Expected
        $dashboardTransactionView = new DashboardTransactionView(100, 25, 75);
        $dashboardSheetView = new DashboardSheetView(100, 150, [], []);
        $dashboardMeetingView = new DashboardMeetingView(200, 20, 10, 300, 20, 100, 33, 32, [42 => 2, 1337 => 120], []);
        $dashboardContactView = new DashboardContactView(
            [42 => [5 => 3, 4 => 7], 1337 => [5 => 1, 3 => 1]],
            [42 => 2, 1337 => 1]
        );
        $dashboardViewExpected = new DashboardView(
            $dashboardTransactionView,
            $dashboardSheetView,
            $dashboardMeetingView,
            $dashboardContactView
        );

        // Mock
        $dashboardTransactionHandler = $this->prophesize(DashboardTransactionViewQueryHandler::class);
        $dashboardSheetHandler = $this->prophesize(DashboardSheetViewQueryHandler::class);
        $dashboardMeetingHandler = $this->prophesize(DashboardMeetingViewQueryHandler::class);
        $dashboardContactViewQueryHandler = $this->prophesize(DashboardContactViewQueryHandler::class);

        $dashboardTransactionHandler->handle(
            new DashboardTransactionViewQuery($event)
        )->shouldBeCalled()->willReturn($dashboardTransactionView);

        $dashboardSheetHandler->handle(
            new DashboardSheetViewQuery($event, $locale)
        )->shouldBeCalled()->willReturn($dashboardSheetView);

        $dashboardMeetingHandler
            ->handle(new DashboardMeetingViewQuery($event))
            ->shouldBeCalled()
            ->willReturn($dashboardMeetingView);

        $dashboardContactViewQueryHandler
            ->handle(new DashboardContactViewQuery($event))
            ->shouldBeCalled()
            ->willReturn($dashboardContactView);

        $handler = new DashboardViewQueryHandler(
            $dashboardTransactionHandler->reveal(),
            $dashboardSheetHandler->reveal(),
            $dashboardMeetingHandler->reveal(),
            $dashboardContactViewQueryHandler->reveal()
        );

        $dashboardView = $handler->handle($query);

        $this->assertEquals($dashboardViewExpected, $dashboardView);
    }
}
