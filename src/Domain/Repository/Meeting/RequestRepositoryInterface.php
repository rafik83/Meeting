<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Meeting;

use Knp\Component\Pager\PaginatorInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

interface RequestRepositoryInterface
{
    /**
     * @param Request $request
     */
    public function add(Request $request);

    /**
     * @param Request $request
     */
    public function set(Request $request);

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return Request[]
     */
    public function getAllRequestBySheet(Sheet $sheet);

    /**
     * @param Event $event
     * @param int   $page
     * @param int   $limit
     *
     * @return PaginatorInterface
     */
    public function getPendingByEvent(Event $event, $page, $limit);

    /**
     * @param Sheet $one
     * @param Sheet $another
     * @param array $state
     *
     * @return Request[]
     */
    public function getRequestBetweenSheetsWithStates(Sheet $one, Sheet $another, array $state);

    /**
     * @param int  $event
     * @param User $user
     *
     * @return Request[]
     */
    public function getRequestsByEventAndUser($event, User $user);
}
