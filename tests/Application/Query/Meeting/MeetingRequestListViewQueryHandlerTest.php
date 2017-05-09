<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class MeetingRequestListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $now      = new \DateTime();
        $event    = EventFactory::createEvent();
        $eventTwo = EventFactory::createEvent();

        $type    = new Type($event);
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $userTwo = new User('otheruser@email.com', 'salt', 'password', 'fr');

        $sheet    = new Sheet($event, $type, [], $user, $now);
        $sheetTwo = new Sheet($eventTwo, new Type($eventTwo), [], $userTwo, $now);

        $meetingRequest = new Request($sheet, [], $sheetTwo, [], $now, $user);

        $query = new MeetingRequestListViewQuery($event, $sheet, $user, 'fr');

        // Expected
        $meetingRequestListView = new MeetingRequestListView();
        $meetingRequestView     = new MeetingRequestView($sheetTwo, '', Request::STATE_SENT, '', $now, $meetingRequest, []);
        $meetingRequestListView->addRequestView($meetingRequestView);

        // Mock
        $meetingRequestRepository             = $this->prophesize(RequestRepositoryInterface::class);
        $meetingRequestViewQueryHandler       = $this->prophesize(MeetingRequestViewQueryHandler::class);
        $meetingPublishedAccessChecker        = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingRequestAccessChecker          = $this->prophesize(MeetingRequestAccessChecker::class);
        $answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $viewedSheetListViewQueryHandler      = $this->prophesize(ViewedSheetListViewQueryHandler::class);

        $meetingRequestRepository
            ->getAllRequestBySheet($sheet, [])
            ->shouldBeCalled()
            ->willReturn([$meetingRequest]);

        $viewedSheetListViewQueryHandler
            ->handle(new ViewedSheetListViewQuery($user, [$sheet->getId()]))
            ->shouldBeCalled();

        $meetingRequestViewQueryHandler
            ->handle(new MeetingRequestViewQuery(
                $meetingRequest,
                $sheet,
                $user,
                'fr',
                false,
                false,
                false,
                false
            ))
            ->shouldBeCalled()
            ->willReturn($meetingRequestView);

        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $meetingRequestAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $answeringMeetingRequestAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        $handler = new MeetingRequestListViewQueryHandler(
            $meetingRequestRepository->reveal(),
            $meetingRequestViewQueryHandler->reveal(),
            $viewedSheetListViewQueryHandler->reveal(),
            $meetingPublishedAccessChecker->reveal(),
            $meetingRequestAccessChecker->reveal(),
            $answeringMeetingRequestAccessChecker->reveal()
        );

        $handler->handle($query);
    }

    public function testHandleWithClosedMeetingRequest()
    {
        $now      = new \DateTime();
        $event    = EventFactory::createEvent();
        $eventTwo = EventFactory::createEvent();

        $type    = new Type($event);
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $userTwo = new User('otheruser@email.com', 'salt', 'password', 'fr');

        $sheet    = new Sheet($event, $type, [], $user, $now);
        $sheetTwo = new Sheet($eventTwo, new Type($eventTwo), [], $userTwo, $now);

        $meetingRequest = new Request($sheet, [], $sheetTwo, [], $now, $user);

        $query = new MeetingRequestListViewQuery($event, $sheet, $user, 'fr');

        // Expected
        $meetingRequestListView = new MeetingRequestListView();
        $meetingRequestView     = new MeetingRequestView($sheetTwo, '', Request::STATE_SENT, '', $now, $meetingRequest, []);
        $meetingRequestListView->addRequestView($meetingRequestView);

        // Mock
        $meetingRequestRepository             = $this->prophesize(RequestRepositoryInterface::class);
        $meetingRequestViewQueryHandler       = $this->prophesize(MeetingRequestViewQueryHandler::class);
        $meetingPublishedAccessChecker        = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingRequestAccessChecker          = $this->prophesize(MeetingRequestAccessChecker::class);
        $answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $viewedSheetListViewQueryHandler      = $this->prophesize(ViewedSheetListViewQueryHandler::class);

        $meetingRequestRepository
            ->getAllRequestBySheet($sheet, [])
            ->shouldBeCalled()
            ->willReturn([$meetingRequest]);

        $viewedSheetListViewQueryHandler
            ->handle(new ViewedSheetListViewQuery($user, [$sheet->getId()]))
            ->shouldBeCalled();

        $meetingRequestViewQueryHandler
            ->handle(new MeetingRequestViewQuery(
                $meetingRequest,
                $sheet,
                $user,
                'fr',
                false,
                false,
                true,
                true
            ))
            ->shouldBeCalled()
            ->willReturn($meetingRequestView);

        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $meetingRequestAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $answeringMeetingRequestAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);

        $handler = new MeetingRequestListViewQueryHandler(
            $meetingRequestRepository->reveal(),
            $meetingRequestViewQueryHandler->reveal(),
            $viewedSheetListViewQueryHandler->reveal(),
            $meetingPublishedAccessChecker->reveal(),
            $meetingRequestAccessChecker->reveal(),
            $answeringMeetingRequestAccessChecker->reveal()
        );

        $handler->handle($query);
    }
}
