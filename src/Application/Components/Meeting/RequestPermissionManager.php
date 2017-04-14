<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Application\Components\Sheet\SheetManager;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class RequestPermissionManager
{
    /**
     * @var MeetingPublishedAccessChecker
     */
    private $meetingPublishedAccessChecker;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var SheetManager
     */
    private $sheetManager;

    /**
     * RequestPermissionManager constructor.
     *
     * @param RequestRepositoryInterface    $requestRepository
     * @param SheetManager                  $sheetManager
     * @param MeetingPublishedAccessChecker $meetingPublishedAccessChecker
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        SheetManager $sheetManager,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker
    ) {
        $this->requestRepository             = $requestRepository;
        $this->sheetManager                  = $sheetManager;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
    }

    /**
     * Is a user allowed to edit a meeting request as a particular sheet
     *
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToEdit(Request $request, Sheet $sheet)
    {
        return $request->isSender($sheet) && $request->isSent();
    }

    /**
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToEditSentOrApproved(Request $request, Sheet $sheet)
    {
        if ($request->isSent()) {
            return $this->isAllowedToEdit($request, $sheet);
        } elseif ($request->isApproved()) {
            return $this->isAllowedToEditApproved($request, $sheet);
        }

        return false;
    }

    /**
     * Is a user allowed to edit an approved meeting request
     *
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToEditApproved(Request $request, Sheet $sheet)
    {
        if (($request->isSender($sheet) || $request->isReceiver($sheet))
            && $request->isApproved()
            && !$sheet->getEvent()->getConfiguration()->isMeetingRequestUpdateLocked()
        ) {
            if ($this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent()) && $request->hasMeeting()) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Is a user allowed to cancel a meeting request
     *
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToCancel(Request $request, Sheet $sheet)
    {
        if ($request->isSender($sheet)) {
            if ($request->isSent()) {
                return true;
            } elseif ($request->isApproved() && !$sheet->getEvent()->getConfiguration()->isMeetingRequestUpdateLocked()) {
                if ($this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent()) && $request->hasMeeting()) {
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Is a user allowed to refuse a meeting request
     *
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToRefuse(Request $request, Sheet $sheet)
    {
        return $request->isReceiver($sheet) && $request->isSent();
    }

    /**
     * Is a user allowed to approve a meeting request
     *
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToApprove(Request $request, Sheet $sheet)
    {
        return $request->isReceiver($sheet) && $request->isSent();
    }

    /**
     * Is a user allowed to see a meeting request
     *
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToSee(Request $request, Sheet $sheet)
    {
        return ($request->isSender($sheet) || $request->isReceiver($sheet));
    }

    /**
     * Is a user allowed to unrefuse a refused meeting request
     *
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToUnRefuse(Request $request, Sheet $sheet)
    {
        return $request->isRefused() && $request->isReceiver($sheet);
    }

    /**
     * Is a user allowed to unapproved an approved meeting request
     *
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToUnApprove(Request $request, Sheet $sheet)
    {
        if ($request->isApproved()
            && $request->isReceiver($sheet)
            && !$sheet->getEvent()->getConfiguration()->isMeetingRequestUpdateLocked()
        ) {
            if ($this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent()) && $request->hasMeeting()) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Is a sheet allowed to see conversation of a refuse meeting request
     *
     * @param Sheet   $sheet
     * @param Request $request
     *
     * @return bool
     */
    public function isAllowedToSeeConversationOfRefuseMeetingRequest(Sheet $sheet, Request $request)
    {
        if (!$request->isRefused()) {
            return false;
        }

        return $request->getFromSheet() === $sheet || $request->getToSheet() === $sheet;
    }

    /**
     * Hasn't living meeting request between these two sheets
     *
     * @param Sheet $from
     * @param Sheet $to
     *
     * @return bool
     */
    public function hasNotLivingRequestsBetween(Sheet $from, Sheet $to)
    {
        return empty($this->requestRepository->getRequestBetweenSheetsWithStates($from, $to, [
            Request::STATE_APPROVED,
            Request::STATE_REFUSED,
            Request::STATE_SENT,
        ]));
    }
}
