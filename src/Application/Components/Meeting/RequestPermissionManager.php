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
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class RequestPermissionManager
{
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
     * @param RequestRepositoryInterface $requestRepository
     * @param SheetManager               $sheetManager
     */
    public function __construct(RequestRepositoryInterface $requestRepository, SheetManager $sheetManager)
    {
        $this->requestRepository = $requestRepository;
        $this->sheetManager      = $sheetManager;
    }

    /**
     * Is a user allowed to edit a meeting request as a particular sheet
     *
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToEdit(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user) && $request->isSender($sheet) && $request->isSent();
    }

    /**
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToEditSentOrApproved(User $user, Request $request, Sheet $sheet)
    {
        if ($request->isSent()) {
            return $this->isAllowedToEdit($user, $request, $sheet);
        } elseif ($request->isApproved()) {
            return $this->isAllowedToEditApproved($user, $request, $sheet);
        }

        return false;
    }

    /**
     * Is a user allowed to edit an approved meeting request
     *
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToEditApproved(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user)
            && ($request->isSender($sheet) || $request->isReceiver($sheet))
            && $request->isApproved()
            && !$request->hasMeeting();
    }

    /**
     * Is a user allowed to cancel a meeting request
     *
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToCancel(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user)
            && $request->isSender($sheet)
            && ($request->isSent() || $request->isApproved())
            && !$request->hasMeeting();
    }

    /**
     * Is a user allowed to refuse a meeting request
     *
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToRefuse(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user) && $request->isReceiver($sheet) && $request->isSent();
    }

    /**
     * Is a user allowed to approve a meeting request
     *
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToApprove(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user) && $request->isReceiver($sheet) && $request->isSent();
    }

    /**
     * Is a user allowed to see a meeting request
     *
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToSee(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user) && ($request->isSender($sheet) || $request->isReceiver($sheet));
    }

    /**
     * Is a user allowed to unrefuse a refused meeting request
     *
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToUnRefuse(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user) && $request->isRefused() && $request->isReceiver($sheet);
    }

    /**
     * Is a user allowed to unapproved an approved meeting request
     *
     * @param User    $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToUnApprove(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user) && $request->isApproved() && $request->isReceiver($sheet);
    }

    /**
     * Is a user allowed to create a meeting request between these two sheets
     *
     * @param User  $user
     * @param Sheet $from
     * @param Sheet $to
     *
     * @return bool
     */
    public function isAllowedToCreate(User $user, Sheet $from, Sheet $to)
    {
        return
            $from->hasUser($user) &&
            $this->sheetManager->isAllowedToSee($user, $from, $to) &&
            $this->hasNotLivingRequestsBetween($from, $to);
    }

    /**
     * Is a user allowed to see conversation of a refuse meeting request
     *
     * @param User    $user
     * @param Request $request
     *
     * @return bool
     */
    public function isAllowedToSeeConversationOfRefuseMeetingRequest(User $user, Request $request)
    {
        if (!$request->isRefused()) {
            return false;
        }

        return $request->getFromSheet()->hasUser($user) || $request->getToSheet()->hasUser($user);
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
