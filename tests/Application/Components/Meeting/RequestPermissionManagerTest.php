<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;

class RequestPermissionManagerTest extends TestCase
{
    /** @varObjectProphecy */
    public $meetingPublishedAccessChecker;

    /** @var ObjectProphecy */
    private $requestRepository;

    /** @var ObjectProphecy */
    private $answeringMeetingRequestAccessChecker;

    /** @var Event */
    private $event;

    /**
     * Init mock for the suite test
     */
    public function setUp()
    {
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $this->answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $this->event = EventFactory::createEvent();
    }

    /**
     * @return RequestPermissionManager
     */
    private function getRequestPermissionManager()
    {
        return new RequestPermissionManager(
            $this->requestRepository->reveal(),
            $this->meetingPublishedAccessChecker->reveal(),
            $this->answeringMeetingRequestAccessChecker->reveal()
        );
    }

    private static function getInitialsValue()
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $datetime = new \DateTime();
        $user     = new User('test@test.fr', 'test', 'test', 'fr');
        $user2    = new User('test2@test.fr', 'test2', 'test2', 'en');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);
        $sheet2   = new Sheet($event, $type, [], $user2, $datetime);
        $request  = new Request($sheet, [], $sheet2, [], $datetime, $user, $event);

        return [
            $datetime,
            $sheet,
            $sheet2,
            $request,
        ];
    }

    public function testIsAllowedToEditFalseAsSheetIsTheToSheet()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEdit(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToEditFalseAsRequestIsApproved()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEdit(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditFalseAsRequestIsRefused()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEdit(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditFalseAsDateForAnsweringIsPassed()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();

        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(false);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEdit(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditTrue()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToEdit(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsRequestIsRefuseAndEditBySheetFrom()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsSheetToTryToEditSentRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsSheetToTryToEditRefuseRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToEditApprovedFalseForSheetFromAndSentRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedTureAsRequestIsPlacedButMeetingNotPublished()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $slot    = new MeetingSlot($sheet->getEvent(), new \DateTime(), new \DateTime(), false);
        $spot    = SpotFactory::create($sheet->getEvent());
        $meeting = new Meeting($request, $slot, $sheet, [], $sheet2, [], new \DateTime(), $spot, $this->event);
        $this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(false);
        $request->setMeeting($meeting);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsMeetingRequestUpdateIsLocked()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $sheet->getEvent()->getConfiguration()->setMeetingRequestUpdateLocked(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsRequestIsPlacedAndMeetingPublished()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $slot    = new MeetingSlot($sheet->getEvent(), new \DateTime(), new \DateTime(), false);
        $spot    = SpotFactory::create($sheet->getEvent());
        $meeting = new Meeting($request, $slot, $sheet, [], $sheet2, [], new \DateTime(), $spot, $this->event);
        $this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);
        $request->setMeeting($meeting);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsDateToAnswerIsPassed()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(false);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedTrueForSheetFromAndApprovedRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsDateAnsweringRequestIsPassed()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(false);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsRequestIsRefuseAndDoneBySheetFrom()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsRequestIsRefuseAndDoneBySheetTo()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsSheetToIsTryingToCancel()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToCancelTrueAsRequestIsSentAndSheetFromIsTryingToCancel()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsMeetingRequestUpdateIsLocked()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);
        $sheet->getEvent()->getConfiguration()->setMeetingRequestUpdateLocked(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsRequestIsPlacedAndMeetingPublished()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);
        $request->approve($datetime);
        $slot    = new MeetingSlot($sheet->getEvent(), new \DateTime(), new \DateTime(), false);
        $spot    = SpotFactory::create($sheet->getEvent());
        $meeting = new Meeting($request, $slot, $sheet, [], $sheet2, [], new \DateTime(), $spot, $this->event);
        $this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);
        $request->setMeeting($meeting);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelTrueAsRequestIsPlacedAndMeetingNotPublished()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);
        $request->approve($datetime);
        $slot    = new MeetingSlot($sheet->getEvent(), new \DateTime(), new \DateTime(), false);
        $spot    = SpotFactory::create($sheet->getEvent());
        $meeting = new Meeting($request, $slot, $sheet, [], $sheet2, [], new \DateTime(), $spot, $this->event);
        $this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(false);
        $request->setMeeting($meeting);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelTrueAsRequestIsApprovedAndSheetFromIsTryingToCancel()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToCancel(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToRefuseFalseAsSheetFromIsTryingToRefuse()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToRefuse(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToRefuseFalseAsSheetToIsTryingToRefuseAnApprovedRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToRefuse(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToRefuseFalseAsDateToAnswerRequestIsPassed()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(false);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToRefuse(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToRefuseTrueAsSheetToIsTryingToRefuseSentRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToRefuse(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToApproveFalseAsSheetFromIsTryingToApprove()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToApprove(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToApproveFalseAsSheetToIsTryingToApproveAnApprovedRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToApproveFalseAsSheetToIsTryingToApproveRefusedRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToApproveFalseAsDateToAnswerIsPassed()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(false);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToApproveTrueAsSheetToIsTryingToApproveSentRequest()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToSeeTrueForSheetFrom()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToSee(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToSeeTrueForSheetTo()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToSee(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnRefuseFalseAsRequestIsNotRefuse()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnRefuseFalseAsItIsDoneBySheetFrom()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnRefuseFalseAsUserIsNotOnSheet()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnRefuseFalseAsDateToAnswerIsPassed()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $request->refuse($datetime);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(false);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnRefuseTrueAsItIsDoneBySheetTo()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $request->refuse($datetime);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnApproveFalseAsDateToAnswerRequestIsPassed()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(false);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnApproveFalseAsItIsDoneBySheetFrom()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnApproveFalseAsSheetDoesNotHaveUser()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnApproveFalseAsRequestIsNotApproved()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnApproveTrueAsRequestIsApprovedAndSheetToIsTryingToUnApproveIt()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnApproveFalseAsMeetingRequestUpdateAreLocked()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);
        $sheet2->getEvent()->getConfiguration()->setMeetingRequestUpdateLocked(true);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnApproveFalseAsMeetingsArePublished()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $slot    = new MeetingSlot($sheet->getEvent(), new \DateTime(), new \DateTime(), false);
        $spot    = SpotFactory::create($sheet->getEvent());
        $meeting = new Meeting($request, $slot, $sheet, [], $sheet2, [], new \DateTime(), $spot, $this->event);
        $this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(true);
        $request->setMeeting($meeting);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnApproveTrueAsMeetingsAreNotPublished()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $slot    = new MeetingSlot($sheet->getEvent(), new \DateTime(), new \DateTime(), false);
        $spot    = SpotFactory::create($sheet->getEvent());
        $meeting = new Meeting($request, $slot, $sheet, [], $sheet2, [], new \DateTime(), $spot, $this->event);
        $this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent())->shouldBeCalled()->willReturn(false);
        $request->setMeeting($meeting);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet2->getEvent())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToSeeConversationOfRefuseMeetingRequestFalseAsRequestIsNotRefused()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToSeeConversationOfRefuseMeetingRequest(
            $sheet2,
            $request
        ));
    }

    public function testIsAllowedToSeeConversationOfRefuseMeetingRequestFalseAsItIsDoneByUnknownUser()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);
        $sheet3 = SheetFactory::create();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToSeeConversationOfRefuseMeetingRequest(
            $sheet3,
            $request
        ));
    }

    public function testIsAllowedToSeeConversationOfRefuseMeetingRequestTrueAsItIsDoneByUser()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToSeeConversationOfRefuseMeetingRequest(
            $sheet,
            $request
        ));
    }

    public function testIsAllowedToSeeConversationOfRefuseMeetingRequestTrueAsItIsDoneByUser2()
    {
        list(
            $datetime,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToSeeConversationOfRefuseMeetingRequest(
            $sheet,
            $request
        ));
    }
}
