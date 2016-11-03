<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Meeting;

use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Components\Sheet\SheetManager;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Prophecy\Prophecy\ObjectProphecy;

class RequestPermissionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $requestRepository;

    /**
     * @var ObjectProphecy
     */
    private $sheetManager;

    /**
     * Init mock for the suite test
     */
    public function setUp()
    {
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->sheetManager      = $this->prophesize(SheetManager::class);
    }

    /**
     * @return RequestPermissionManager
     */
    private function getRequestPermissionManager()
    {
        return new RequestPermissionManager(
            $this->requestRepository->reveal(),
            $this->sheetManager->reveal()
        );
    }

    private static function getInitialsValue()
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $datetime = new \DateTime();
        $user     = new User('test@test.fr', 'test', 'test', 'fr');
        $user2    = new User('test@test.fr', 'test', 'test', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);
        $sheet2   = new Sheet($event, $type, [], $user2, $datetime);
        $request  = new Request($sheet, [], $sheet2, [], $datetime, $user);

        return [
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request,
        ];
    }

    public function testIsAllowedToEditFalseAsUserIsNotOnSheet()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEdit(
            $user2,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditFalseAsSheetIsTheToSheet()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEdit(
            $user2,
            $request,
            $sheet2
        ));
    }
    public function testIsAllowedToEditFalseAsRequestIsApproved()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEdit(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditFalseAsRequestIsRefused()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEdit(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditTrue()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToEdit(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsRequestIsRefuseAndEditBySheetFrom()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsUserIsNotOnSheet()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $user,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsSheetToTryToEditSentRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToEditApprovedFalseAsSheetToTryToEditRefuseRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToEditApprovedFalseForSheetFromAndSentRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToEditApprovedTrueForSheetFromAndApprovedRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToEditApproved(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsRequestIsRefuseAndDoneBySheetFrom()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsRequestIsRefuseAndDoneBySheetTo()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelFalseAsSheetToIsTryingToCancel()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCancel(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToCancelTrueAsRequestIsSentAndSheetFromIsTryingToCancel()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToCancel(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToCancelTrueAsRequestIsApprovedAndSheetFromIsTryingToCancel()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToCancel(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToRefuseFalseAsSheetFromIsTryingToRefuse()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToRefuse(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToRefuseFalseAsSheetToIsTryingToRefuseAnApprovedRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToRefuse(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToRefuseTrueAsSheetToIsTryingToRefuseSentRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToRefuse(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToApproveFalseAsSheetFromIsTryingToApprove()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToApprove(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToApproveFalseAsSheetToIsTryingToApproveAnApprovedRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToApprove(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToApproveFalseAsSheetToIsTryingToApproveRefusedRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToApprove(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToApproveTrueAsSheetToIsTryingToApproveSentRequest()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToApprove(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToSeeFalseAsUserIsNotPartOfSheet()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToSee(
            $user,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToSeeTrueForSheetFrom()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToSee(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToSeeTrueForSheetTo()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToSee(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnRefuseFalseAsRequestIsNotRefuse()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToUnRefuseFalseAsItIsDoneBySheetFrom()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnRefuseFalseAsUserIsNotOnSheet()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $user2,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnRefuseTrueAsItIsDoneBySheetTo()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
            ) = $this->getInitialsValue();
        $request->refuse($datetime);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToUnRefuse(
            $user2,
            $request,
            $sheet2
        ));
    }

    public function testIsAllowedToCreateFalseAsSheetFromCanNotSeeSheetTo()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->sheetManager->isAllowedToSee($user, $sheet, $sheet2)->shouldBeCalled()->willReturn(false);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCreate(
            $user,
            $sheet,
            $sheet2
        ));
    }

    public function testIsAllowedToCreateFalseAsSheetFromHasAlreayRequestWithSheetTo()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->sheetManager->isAllowedToSee($user, $sheet, $sheet2)->shouldBeCalled()->willReturn(true);
        $this->requestRepository->getRequestBetweenSheetsWithStates($sheet, $sheet2, [
            Request::STATE_APPROVED,
            Request::STATE_REFUSED,
            Request::STATE_SENT,
        ])->shouldBeCalled()->willReturn([$request]);

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToCreate(
            $user,
            $sheet,
            $sheet2
        ));
    }

    public function testIsAllowedToCreateTrueForSheet()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->sheetManager->isAllowedToSee($user, $sheet, $sheet2)->shouldBeCalled()->willReturn(true);
        $this->requestRepository->getRequestBetweenSheetsWithStates($sheet, $sheet2, [
            Request::STATE_APPROVED,
            Request::STATE_REFUSED,
            Request::STATE_SENT,
        ])->shouldBeCalled()->willReturn([]);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToCreate(
            $user,
            $sheet,
            $sheet2
        ));
    }

    public function testIsAllowedToCreateTrueForSheet2()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->sheetManager->isAllowedToSee($user2, $sheet2, $sheet)->shouldBeCalled()->willReturn(true);
        $this->requestRepository->getRequestBetweenSheetsWithStates($sheet2, $sheet, [
            Request::STATE_APPROVED,
            Request::STATE_REFUSED,
            Request::STATE_SENT,
        ])->shouldBeCalled()->willReturn([]);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToCreate(
            $user2,
            $sheet2,
            $sheet
        ));
    }

    public function testIsAllowedToUnApproveFalseAsItIsDoneBySheetFrom()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $user,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnApproveFalseAsSheetDoesNotHaveUser()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $user2,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnApproveFalseAsRequestIsNotApproved()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();

        $this->assertEquals(false, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $user2,
            $request,
            $sheet
        ));
    }

    public function testIsAllowedToUnApproveTrueAsRequestIsApprovedAndSheetToIsTryingToUnApproveIt()
    {
        list(
            $datetime,
            $user,
            $user2,
            $sheet,
            $sheet2,
            $request
        ) = $this->getInitialsValue();
        $request->approve($datetime);

        $this->assertEquals(true, $this->getRequestPermissionManager()->isAllowedToUnApprove(
            $user2,
            $request,
            $sheet2
        ));
    }
}
