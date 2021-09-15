<?php

namespace Proximum\Vimeet\Tests\Application\Query\Group\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\FilterRequestView;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\RequestViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\RequestViewQueryHandler;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetListViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetListViewQueryHandler;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\RequestView;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetListView;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user          = $this->prophesize(User::class);
        $event         = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Configuration::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration->isMeetingRequestUpdateLocked()->shouldBeCalled()->willReturn(false);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet2 = $this->prophesize(Sheet::class);

        $multipleSheets = [
            1 => $sheet1->reveal(),
            2 => $sheet2->reveal(),
        ];

        $sheetsMet = [
            $sheetMet1->reveal(),
            $sheetMet2->reveal(),
            $sheet1->reveal(),
            $sheet2->reveal(),
        ];

        $sheet1->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheetMet1->getId()->shouldBeCalled()->willReturn(3);
        $sheetMet2->getId()->shouldBeCalled()->willReturn(4);

        $locale = 'fr';
        $page   = 1;
        $limit  = 4; // To ease test

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $requestRepository       = $this->prophesize(RequestRepositoryInterface::class);
        $sheetViewQueryHandler   = $this->prophesize(SheetViewQueryHandler::class);
        $requestViewQueryHandler = $this->prophesize(RequestViewQueryHandler::class);

        $sheetRepository
            ->getSheetsMetBySheetsPaginated($event->reveal(), $multipleSheets, 1, 4, null, null, null)
            ->shouldBeCalled()
            ->willReturn(new PaginatedResult($sheetsMet, 1, 4, 5, []));

        $sheetRepository
            ->getSheetsMetBySheets($event->reveal(), $multipleSheets, null, null, null)
            ->shouldBeCalled()
            ->willReturn($sheetsMet);

        $request1 = $this->prophesize(Request::class);
        $request1->getFromSheet()->shouldBeCalled()->willReturn($sheetMet1->reveal());
        $request1->getToSheet()->willReturn($sheet1->reveal());

        $request2 = $this->prophesize(Request::class);
        $request2->getFromSheet()->shouldBeCalled()->willReturn($sheetMet2->reveal());
        $request2->getToSheet()->willReturn($sheet1->reveal());

        $request3 = $this->prophesize(Request::class);
        $request3->getFromSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $request3->getToSheet()->shouldBeCalled()->willReturn($sheet2->reveal());

        $requestRepository
            ->getRequestsOfSheetsWithSheets(
                $event->reveal(),
                $multipleSheets,
                $sheetsMet,
                null,
                null,
                null
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $request1->reveal(),
                    $request2->reveal(),
                    $request3->reveal(),
                ]
            );

        $requestView1 = $this->prophesize(RequestView::class);
        $requestView2 = $this->prophesize(RequestView::class);
        $requestView3 = $this->prophesize(RequestView::class);

        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet1->reveal(), $request1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView1);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet2->reveal(), $request2->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView2);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheet1->reveal(), $request3->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView3);

        $sheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $sheetView2 = new SheetView(4, 'B', $sheetMet2->reveal());
        $sheetView3 = new SheetView(1, 'C', $sheet1->reveal());
        $sheetView4 = new SheetView(2, 'D', $sheet2->reveal());

        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet1->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView1);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet2->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView2);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet1->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView3);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet2->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView4);

        $expectedSheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $expectedSheetView1->addRequest($requestView1->reveal());

        $expectedSheetView2 = new SheetView(4, 'B', $sheetMet2->reveal());
        $expectedSheetView2->addRequest($requestView2->reveal());

        $expectedSheetView3 = new SheetView(1, 'C', $sheet1->reveal());
        $expectedSheetView3->addRequest($requestView3->reveal());

        $meetingRequestAccessChecker          = $this->prophesize(MeetingRequestAccessChecker::class);
        $answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $meetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $answeringMeetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $requestRepository
            ->countRequestOfSheetsWithSheets(
                $event,
                $multipleSheets,
                $sheetsMet,
                null,
                null,
                null
            )
            ->shouldBeCalled()
            ->willReturn(12);

        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $requestRepository->reveal(),
            $sheetViewQueryHandler->reveal(),
            $requestViewQueryHandler->reveal(),
            $meetingRequestAccessChecker->reveal(),
            $answeringMeetingRequestAccessChecker->reveal()
        );

        $filterRequestView = new FilterRequestView();
        $result            = $handler->handle(
            new SheetListViewQuery($user->reveal(), [
                1 => $sheet1->reveal(),
                2 => $sheet2->reveal(),
            ], $locale, $page, $limit, $filterRequestView)
        );

        $expected = new SheetListView(
            [3 => $expectedSheetView1, 4 => $expectedSheetView2, 1 => $expectedSheetView3],
            1,
            2,
            12,
            false,
            false,
            false
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithOtherSheet()
    {
        $user          = $this->prophesize(User::class);
        $event         = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Configuration::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration->isMeetingRequestUpdateLocked()->shouldBeCalled()->willReturn(false);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheet1->getId()->shouldBeCalled()->willReturn(1);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);

        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet1->getId()->shouldBeCalled()->willReturn(3);

        $multipleSheets = [
            1 => $sheet1->reveal(),
            2 => $sheet2->reveal(),
        ];

        $sheetsMet = [
            $sheetMet1->reveal(),
        ];

        $locale = 'fr';
        $page   = 1;
        $limit  = 4; // To ease test

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $requestRepository       = $this->prophesize(RequestRepositoryInterface::class);
        $sheetViewQueryHandler   = $this->prophesize(SheetViewQueryHandler::class);
        $requestViewQueryHandler = $this->prophesize(RequestViewQueryHandler::class);

        $sheetRepository
            ->getSheetsMetBySheetsPaginated($event->reveal(), $multipleSheets, 1, 4, null, null, null)
            ->shouldNotBeCalled();

        $sheetRepository
            ->getSheetsMetBySheets($event->reveal(), $multipleSheets, null, null, null)
            ->shouldNotBeCalled();

        $request1 = $this->prophesize(Request::class);
        $request1->getFromSheet()->shouldBeCalled()->willReturn($sheetMet1->reveal());
        $request1->getToSheet()->willReturn($sheet1->reveal());

        $requestRepository
            ->getRequestsOfSheetsWithSheets(
                $event->reveal(),
                $multipleSheets,
                $sheetsMet,
                null,
                null,
                null
            )
            ->shouldBeCalled()
            ->willReturn([$request1->reveal()]);

        $requestRepository
            ->countRequestOfSheetsWithSheets(
                $event->reveal(),
                $multipleSheets,
                $sheetsMet,
                null,
                null,
                null
            )
            ->shouldBeCalled()
            ->willReturn(12);

        $requestView1 = $this->prophesize(RequestView::class);

        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet1->reveal(), $request1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView1);

        $sheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());

        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet1->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView1);

        $expectedSheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $expectedSheetView1->addRequest($requestView1->reveal());

        $meetingRequestAccessChecker          = $this->prophesize(MeetingRequestAccessChecker::class);
        $answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $meetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $answeringMeetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $requestRepository->reveal(),
            $sheetViewQueryHandler->reveal(),
            $requestViewQueryHandler->reveal(),
            $meetingRequestAccessChecker->reveal(),
            $answeringMeetingRequestAccessChecker->reveal()
        );

        $filterRequestView             = new FilterRequestView();
        $filterRequestView->otherSheet = $sheetMet1->reveal();
        $result                        = $handler->handle(
            new SheetListViewQuery(
                $user->reveal(),
                [
                    1 => $sheet1->reveal(),
                    2 => $sheet2->reveal(),
                ], $locale, $page, $limit, $filterRequestView)
        );

        $expected = new SheetListView(
            [3 => $expectedSheetView1],
            1,
            1,
            12,
            false,
            false,
            false
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithType()
    {
        $user          = $this->prophesize(User::class);
        $event         = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Configuration::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration->isMeetingRequestUpdateLocked()->shouldBeCalled()->willReturn(false);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet2 = $this->prophesize(Sheet::class);

        $multipleSheets = [
            1 => $sheet1->reveal(),
            2 => $sheet2->reveal(),
        ];

        $sheetsMet = [
            $sheetMet1->reveal(),
            $sheetMet2->reveal(),
            $sheet1->reveal(),
            $sheet2->reveal(),
        ];

        $sheet1->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheetMet1->getId()->shouldBeCalled()->willReturn(3);
        $sheetMet2->getId()->shouldBeCalled()->willReturn(4);

        $locale = 'fr';
        $page   = 1;
        $limit  = 4; // To ease test

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $requestRepository       = $this->prophesize(RequestRepositoryInterface::class);
        $sheetViewQueryHandler   = $this->prophesize(SheetViewQueryHandler::class);
        $requestViewQueryHandler = $this->prophesize(RequestViewQueryHandler::class);

        $sheetRepository
            ->getSheetsMetBySheetsPaginated($event->reveal(), $multipleSheets, 1, 4, null, Request::TYPE_REQUEST, null)
            ->shouldBeCalled()
            ->willReturn(new PaginatedResult($sheetsMet, 1, 4, 5, []));

        $sheetRepository
            ->getSheetsMetBySheets($event->reveal(), $multipleSheets, null, Request::TYPE_REQUEST, null)
            ->shouldBeCalled()
            ->willReturn($sheetsMet);

        $request1 = $this->prophesize(Request::class);
        $request1->getFromSheet()->shouldBeCalled()->willReturn($sheetMet1->reveal());
        $request1->getToSheet()->willReturn($sheet1->reveal());

        $request2 = $this->prophesize(Request::class);
        $request2->getFromSheet()->shouldBeCalled()->willReturn($sheetMet2->reveal());
        $request2->getToSheet()->willReturn($sheet1->reveal());

        $request3 = $this->prophesize(Request::class);
        $request3->getFromSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $request3->getToSheet()->shouldBeCalled()->willReturn($sheet2->reveal());

        $requestRepository
            ->getRequestsOfSheetsWithSheets(
                $event->reveal(),
                $multipleSheets,
                $sheetsMet,
                null,
                Request::TYPE_REQUEST,
                null
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $request1->reveal(),
                    $request2->reveal(),
                    $request3->reveal(),
                ]
            );

        $requestView1 = $this->prophesize(RequestView::class);
        $requestView2 = $this->prophesize(RequestView::class);
        $requestView3 = $this->prophesize(RequestView::class);

        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet1->reveal(), $request1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView1);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet2->reveal(), $request2->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView2);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheet1->reveal(), $request3->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView3);

        $sheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $sheetView2 = new SheetView(4, 'B', $sheetMet2->reveal());
        $sheetView3 = new SheetView(1, 'C', $sheet1->reveal());
        $sheetView4 = new SheetView(2, 'D', $sheet2->reveal());

        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet1->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView1);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet2->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView2);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet1->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView3);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet2->reveal(), $user->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView4);

        $expectedSheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $expectedSheetView1->addRequest($requestView1->reveal());

        $expectedSheetView2 = new SheetView(4, 'B', $sheetMet2->reveal());
        $expectedSheetView2->addRequest($requestView2->reveal());

        $expectedSheetView3 = new SheetView(1, 'C', $sheet1->reveal());
        $expectedSheetView3->addRequest($requestView3->reveal());

        $meetingRequestAccessChecker          = $this->prophesize(MeetingRequestAccessChecker::class);
        $answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $meetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $answeringMeetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $requestRepository
            ->countRequestOfSheetsWithSheets(
                $event,
                $multipleSheets,
                $sheetsMet,
                null,
                Request::TYPE_REQUEST,
                null
            )
            ->shouldBeCalled()
            ->willReturn(12);

        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $requestRepository->reveal(),
            $sheetViewQueryHandler->reveal(),
            $requestViewQueryHandler->reveal(),
            $meetingRequestAccessChecker->reveal(),
            $answeringMeetingRequestAccessChecker->reveal()
        );

        $filterRequestView       = new FilterRequestView();
        $filterRequestView->type = Request::TYPE_REQUEST;
        $result                  = $handler->handle(
            new SheetListViewQuery(
                $user->reveal(),
                [
                    1 => $sheet1->reveal(),
                    2 => $sheet2->reveal(),
                ], $locale, $page, $limit, $filterRequestView)
        );

        $expected = new SheetListView(
            [3 => $expectedSheetView1, 4 => $expectedSheetView2, 1 => $expectedSheetView3],
            1,
            2,
            12,
            false,
            false,
            false
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithTypeStateAndUser()
    {
        $loggedUser    = $this->prophesize(User::class);
        $user          = $this->prophesize(User::class);
        $event         = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Configuration::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration->isMeetingRequestUpdateLocked()->shouldBeCalled()->willReturn(false);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet2 = $this->prophesize(Sheet::class);

        $multipleSheets = [
            1 => $sheet1->reveal(),
            2 => $sheet2->reveal(),
        ];

        $sheetsMet = [
            $sheetMet1->reveal(),
            $sheetMet2->reveal(),
            $sheet1->reveal(),
            $sheet2->reveal(),
        ];

        $sheet1->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheetMet1->getId()->shouldBeCalled()->willReturn(3);
        $sheetMet2->getId()->shouldBeCalled()->willReturn(4);

        $locale = 'fr';
        $page   = 1;
        $limit  = 4; // To ease test

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $requestRepository       = $this->prophesize(RequestRepositoryInterface::class);
        $sheetViewQueryHandler   = $this->prophesize(SheetViewQueryHandler::class);
        $requestViewQueryHandler = $this->prophesize(RequestViewQueryHandler::class);

        $sheetRepository
            ->getSheetsMetBySheetsPaginated($event->reveal(), $multipleSheets, 1, 4, Request::STATE_SENT, Request::TYPE_REQUEST, $user->reveal())
            ->shouldBeCalled()
            ->willReturn(new PaginatedResult($sheetsMet, 1, 4, 5, []));

        $sheetRepository
            ->getSheetsMetBySheets($event->reveal(), $multipleSheets, Request::STATE_SENT, Request::TYPE_REQUEST, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetsMet);

        $request1 = $this->prophesize(Request::class);
        $request1->getFromSheet()->shouldBeCalled()->willReturn($sheetMet1->reveal());
        $request1->getToSheet()->willReturn($sheet1->reveal());

        $request2 = $this->prophesize(Request::class);
        $request2->getFromSheet()->shouldBeCalled()->willReturn($sheetMet2->reveal());
        $request2->getToSheet()->willReturn($sheet1->reveal());

        $request3 = $this->prophesize(Request::class);
        $request3->getFromSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $request3->getToSheet()->shouldBeCalled()->willReturn($sheet2->reveal());

        $requestRepository
            ->getRequestsOfSheetsWithSheets(
                $event->reveal(),
                $multipleSheets,
                $sheetsMet,
                Request::STATE_SENT,
                Request::TYPE_REQUEST,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $request1->reveal(),
                    $request2->reveal(),
                    $request3->reveal(),
                ]
            );

        $requestRepository
            ->countRequestOfSheetsWithSheets(
                $event,
                $multipleSheets,
                $sheetsMet,
                Request::STATE_SENT,
                Request::TYPE_REQUEST,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(12);

        $requestView1 = $this->prophesize(RequestView::class);
        $requestView2 = $this->prophesize(RequestView::class);
        $requestView3 = $this->prophesize(RequestView::class);

        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet1->reveal(), $request1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView1);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet2->reveal(), $request2->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView2);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheet1->reveal(), $request3->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView3);

        $sheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $sheetView2 = new SheetView(4, 'B', $sheetMet2->reveal());
        $sheetView3 = new SheetView(1, 'C', $sheet1->reveal());
        $sheetView4 = new SheetView(2, 'D', $sheet2->reveal());

        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet1->reveal(), $loggedUser->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView1);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet2->reveal(), $loggedUser->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView2);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet1->reveal(), $loggedUser->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView3);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet2->reveal(), $loggedUser->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView4);

        $expectedSheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $expectedSheetView1->addRequest($requestView1->reveal());

        $expectedSheetView2 = new SheetView(4, 'B', $sheetMet2->reveal());
        $expectedSheetView2->addRequest($requestView2->reveal());

        $expectedSheetView3 = new SheetView(1, 'C', $sheet1->reveal());
        $expectedSheetView3->addRequest($requestView3->reveal());

        $meetingRequestAccessChecker          = $this->prophesize(MeetingRequestAccessChecker::class);
        $answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $meetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $answeringMeetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $requestRepository->reveal(),
            $sheetViewQueryHandler->reveal(),
            $requestViewQueryHandler->reveal(),
            $meetingRequestAccessChecker->reveal(),
            $answeringMeetingRequestAccessChecker->reveal()
        );

        $filterRequestView        = new FilterRequestView();
        $filterRequestView->type  = Request::TYPE_REQUEST;
        $filterRequestView->state = Request::STATE_SENT;
        $filterRequestView->user  = $user->reveal();

        $result = $handler->handle(
            new SheetListViewQuery(
                $loggedUser->reveal(),
                [
                    1 => $sheet1->reveal(),
                    2 => $sheet2->reveal(),
                ], $locale, $page, $limit, $filterRequestView)
        );

        $expected = new SheetListView(
            [3 => $expectedSheetView1, 4 => $expectedSheetView2, 1 => $expectedSheetView3],
            1,
            2,
            12,
            false,
            false,
            false
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithTypeStateAndUserAndSheetConcerned()
    {
        $loggedUser    = $this->prophesize(User::class);
        $user          = $this->prophesize(User::class);
        $event         = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Configuration::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration->isMeetingRequestUpdateLocked()->shouldBeCalled()->willReturn(false);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet2 = $this->prophesize(Sheet::class);

        $sheetsMet = [
            $sheetMet1->reveal(),
            $sheetMet2->reveal(),
            $sheet2->reveal(),
        ];

        $sheet1->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheetMet1->getId()->shouldBeCalled()->willReturn(3);
        $sheetMet2->getId()->shouldBeCalled()->willReturn(4);

        $locale = 'fr';
        $page   = 1;
        $limit  = 4; // To ease test

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $requestRepository       = $this->prophesize(RequestRepositoryInterface::class);
        $sheetViewQueryHandler   = $this->prophesize(SheetViewQueryHandler::class);
        $requestViewQueryHandler = $this->prophesize(RequestViewQueryHandler::class);

        $sheetRepository
            ->getSheetsMetBySheetsPaginated($event->reveal(), [1 => $sheet1->reveal()], 1, 4, Request::STATE_SENT, Request::TYPE_REQUEST, $user->reveal())
            ->shouldBeCalled()
            ->willReturn(new PaginatedResult($sheetsMet, 1, 4, 5, []));

        $sheetRepository
            ->getSheetsMetBySheets($event->reveal(), [1 => $sheet1->reveal()], Request::STATE_SENT, Request::TYPE_REQUEST, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetsMet);

        $request1 = $this->prophesize(Request::class);
        $request1->getFromSheet()->shouldBeCalled()->willReturn($sheetMet1->reveal());
        $request1->getToSheet()->willReturn($sheet1->reveal());

        $request2 = $this->prophesize(Request::class);
        $request2->getFromSheet()->shouldBeCalled()->willReturn($sheetMet2->reveal());
        $request2->getToSheet()->willReturn($sheet1->reveal());

        $request3 = $this->prophesize(Request::class);
        $request3->getFromSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $request3->getToSheet()->shouldBeCalled()->willReturn($sheet2->reveal());

        $requestRepository
            ->getRequestsOfSheetsWithSheets(
                $event->reveal(),
                [1 => $sheet1->reveal()],
                $sheetsMet,
                Request::STATE_SENT,
                Request::TYPE_REQUEST,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $request1->reveal(),
                    $request2->reveal(),
                    $request3->reveal(),
                ]
            );

        $requestRepository
            ->countRequestOfSheetsWithSheets(
                $event,
                [1 => $sheet1->reveal()],
                $sheetsMet,
                Request::STATE_SENT,
                Request::TYPE_REQUEST,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(12);

        $requestView1 = $this->prophesize(RequestView::class);
        $requestView2 = $this->prophesize(RequestView::class);
        $requestView3 = $this->prophesize(RequestView::class);

        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet1->reveal(), $request1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView1);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet2->reveal(), $request2->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView2);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheet2->reveal(), $request3->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView3);

        $sheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $sheetView2 = new SheetView(4, 'B', $sheetMet2->reveal());
        $sheetView4 = new SheetView(2, 'D', $sheet2->reveal());

        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet1->reveal(), $loggedUser->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView1);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet2->reveal(), $loggedUser->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView2);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet2->reveal(), $loggedUser->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView4);

        $expectedSheetView1 = new SheetView(3, 'A', $sheetMet1->reveal());
        $expectedSheetView1->addRequest($requestView1->reveal());

        $expectedSheetView2 = new SheetView(4, 'B', $sheetMet2->reveal());
        $expectedSheetView2->addRequest($requestView2->reveal());

        $expectedSheetView3 = new SheetView(2, 'D', $sheet2->reveal());
        $expectedSheetView3->addRequest($requestView3->reveal());

        $meetingRequestAccessChecker          = $this->prophesize(MeetingRequestAccessChecker::class);
        $answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $meetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $answeringMeetingRequestAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $requestRepository->reveal(),
            $sheetViewQueryHandler->reveal(),
            $requestViewQueryHandler->reveal(),
            $meetingRequestAccessChecker->reveal(),
            $answeringMeetingRequestAccessChecker->reveal()
        );

        $filterRequestView                 = new FilterRequestView();
        $filterRequestView->type           = Request::TYPE_REQUEST;
        $filterRequestView->state          = Request::STATE_SENT;
        $filterRequestView->user           = $user->reveal();
        $filterRequestView->sheetConcerned = $sheet1->reveal();

        $result = $handler->handle(
            new SheetListViewQuery(
                $loggedUser->reveal(),
                [
                    1 => $sheet1->reveal(),
                    2 => $sheet2->reveal(),
                ], $locale, $page, $limit, $filterRequestView)
        );

        $expected = new SheetListView(
            [3 => $expectedSheetView1, 4 => $expectedSheetView2, 2 => $expectedSheetView3],
            1,
            2,
            12,
            false,
            false,
            false
        );

        $this->assertEquals($expected, $result);
    }
}
