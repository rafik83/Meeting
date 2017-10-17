<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\MeetingRequest\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\MeetingRequestListViewQueryHandler;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\MeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\MeetingRequestViewQueryHandler;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestListView;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingRequestListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getTimeZone()->willReturn('Europe/Paris');
        $event->getFallback()->willReturn('fr');
        $request1 = $this->prophesize(Request::class);
        $request2 = $this->prophesize(Request::class);
        $request3 = $this->prophesize(Request::class);
        $requests = [
            $request1->reveal(),
            $request2->reveal(),
            $request3->reveal(),
        ];
        $requestView1 = $this->prophesize(MeetingRequestView::class);
        $requestView2 = $this->prophesize(MeetingRequestView::class);
        $requestView3 = $this->prophesize(MeetingRequestView::class);

        // Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository
            ->findByEventWithHydratationOfElement($event->reveal())
            ->shouldBeCalled()
            ->willReturn($requests)
        ;

        $meetingRequestViewQueryHandler = $this->prophesize(MeetingRequestViewQueryHandler::class);
        $meetingRequestViewQueryHandler
            ->handle(new MeetingRequestViewQuery($request1->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($requestView1->reveal())
        ;
        $meetingRequestViewQueryHandler
            ->handle(new MeetingRequestViewQuery($request2->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($requestView2->reveal())
        ;
        $meetingRequestViewQueryHandler
            ->handle(new MeetingRequestViewQuery($request3->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($requestView3->reveal())
        ;

        $handler = new MeetingRequestListViewQueryHandler(
            $requestRepository->reveal(),
            $meetingRequestViewQueryHandler->reveal()
        );
        $result = $handler->handle(new MeetingRequestListViewQuery($event->reveal()));
        $expected = new MeetingRequestListView(
            [
                $requestView1->reveal(),
                $requestView2->reveal(),
                $requestView3->reveal()
            ],
            'Europe/Paris',
            'fr'
        );

        $this->assertEquals($expected, $result);
    }
}
