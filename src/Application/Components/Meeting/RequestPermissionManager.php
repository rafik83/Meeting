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
     * @param User    $user
     * @param Request $request
     *
     * @return bool
     */
    public function isAllowedToEdit(User $user, Request $request)
    {
        if ($request->getFromSheet()->hasUser($user) && !$request->isRefused()) {
            return true;
        }

        if ($request->getToSheet()->hasUser($user) && $request->isApproved()) {
            return true;
        }

        return false;
    }

    /**
     * @param User    $user
     * @param Request $request
     *
     * @return bool
     */
    public function isAllowedToCancel(User $user, Request $request)
    {
        if ($request->getFromSheet()->hasUser($user) && ($request->isSent() || $request->isApproved())) {
            return true;
        }

        if ($request->getToSheet()->hasUser($user) && $request->isApproved()) {
            return true;
        }

        return false;
    }

    /**
     * @param User    $user
     * @param Request $request
     *
     * @return bool
     */
    public function isAllowedToRefuse(User $user, Request $request)
    {
        if ($request->getToSheet()->hasUser($user) && $request->isSent()) {
            return true;
        }

        return false;
    }

    /**
     * @param User    $user
     * @param Request $request
     *
     * @return bool
     */
    public function isAllowedToApprove(User $user, Request $request)
    {
        if ($request->getToSheet()->hasUser($user) && $request->isSent()) {
            return true;
        }

        return false;
    }

    /**
     * @param User    $user
     * @param Request $request
     *
     * @return bool
     */
    public function isAllowedToSee(User $user, Request $request)
    {
        return true;
    }

    /**
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
            $this->isAllowedToSee($user, $to) &&
            $this->hasNotLivingRequestsBetween($from, $to);
    }

    /**
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
