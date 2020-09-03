<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class RequestPermissionManager
{
    /** @var MeetingPublishedAccessChecker */
    private $meetingPublishedAccessChecker;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var AnsweringMeetingRequestAccessChecker */
    private $answeringMeetingRequestAccessChecker;

    /**
     * @var AllowTransformRequestIntoMeeting
     */
    private $allowTransformRequestIntoMeeting;

    public function __construct(
        AllowTransformRequestIntoMeeting $allowTransformRequestIntoMeeting,
        RequestRepositoryInterface $requestRepository,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker
    ) {
        $this->requestRepository                    = $requestRepository;
        $this->meetingPublishedAccessChecker        = $meetingPublishedAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->allowTransformRequestIntoMeeting = $allowTransformRequestIntoMeeting;
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
        return $request->isSender($sheet)
            && $request->isSent()
            && $this->answeringMeetingRequestAccessChecker->allowedToAccess($request->getEvent())
        ;
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
            && $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())
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
        if ($this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())) {
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
        return $request->isReceiver($sheet)
            && $request->isSent()
            && $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent());
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
        if ($this->allowTransformRequestIntoMeeting->__invoke($request)) {
            return true;
        }

        return $request->isReceiver($sheet)
            && $request->isSent()
            && $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())
        ;
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
        return $request->isSender($sheet) || $request->isReceiver($sheet);
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
        return $request->isRefused()
            && $request->isReceiver($sheet)
            && $this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())
        ;
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
        if ($this->answeringMeetingRequestAccessChecker->allowedToAccess($sheet->getEvent())) {
            if ($request->isApproved()
                && $request->isReceiver($sheet)
                && !$sheet->getEvent()->getConfiguration()->isMeetingRequestUpdateLocked()
            ) {
                if ($this->meetingPublishedAccessChecker->allowedToAccess($sheet->getEvent()) && $request->hasMeeting()) {
                    return false;
                }

                return true;
            }
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
