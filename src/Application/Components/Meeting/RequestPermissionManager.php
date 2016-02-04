<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Meeting;

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
     * RequestPermissionManager constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
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
        if (!$sheet->hasUser($user)) {
            return false;
        }

        if ($sheet === $request->getFromSheet() && !$request->isRefused() && !$request->isCancelled()) {
            return true;
        }

        if ($sheet === $request->getToSheet() && $request->isApproved()) {
            return true;
        }

        return false;
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
        if (!$sheet->hasUser($user)) {
            return false;
        }

        if ($request->getFromSheet() === $sheet && ($request->isSent() || $request->isApproved())) {
            return true;
        }

        if ($request->getToSheet() === $sheet && $request->isApproved()) {
            return true;
        }

        return false;
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
        if (!$sheet->hasUser($user)) {
            return false;
        }

        if ($request->getToSheet() === $sheet && $request->isSent()) {
            return true;
        }

        return false;
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
        if (!$sheet->hasUser($user)) {
            return false;
        }

        if ($request->getToSheet() === $sheet && $request->isSent()) {
            return true;
        }

        return false;
    }

    /**
     * Is a user allowed to see a meeting request
     *
     * @param User $user
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return bool
     */
    public function isAllowedToSee(User $user, Request $request, Sheet $sheet)
    {
        return $sheet->hasUser($user) && ($request->getFromSheet() === $sheet || $request->getToSheet() === $sheet);
    }

    /**
     * Is a user allowed to see a sheet
     *
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function isAllowedToSeeSheet(User $user, Sheet $sheet)
    {
        return true;
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
            $this->isAllowedToSeeSheet($user, $to) &&
            $this->hasNotLivingRequestsBetween($from, $to);
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
            Request::STATE_SENT,
        ]));
    }
}
