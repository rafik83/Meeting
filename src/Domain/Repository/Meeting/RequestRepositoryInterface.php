<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Meeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
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
    public function getApprovedRequestSentBySheet(Sheet $sheet);

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
    public function getApprovedPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     * @param array $filters
     *
     * @return Request[]
     */
    public function getAllRequestBySheet(Sheet $sheet, array $filters = []);

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countAllByEvent(Event $event);

    /**
     * @param Event  $event
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     * @param array  $filter
     *
     * @return PaginatedResult
     */
    public function findByEventAndFilterByState(Event $event, $page, $limit, $locale, array $filter = []);

    /**
     * @param Sheet $one
     * @param Sheet $another
     * @param array $state
     *
     * @return Request[]
     */
    public function getRequestBetweenSheetsWithStates(Sheet $one, Sheet $another, array $state);

    /**
     * @param Sheet $one
     * @param Sheet $another
     *
     * @return Request|null
     */
    public function getRequestBetweenSheets(Sheet $one, Sheet $another);

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return Request[]
     */
    public function getRequestsByEventAndUser(Event $event, User $user);

    /**
     * @param Sheet  $sheet
     * @param string $state
     *
     * @return int
     */
    public function countSheetState(Sheet $sheet, $state);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countApprovedRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countRefusedRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countPendingRequestSentBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countApprovedPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countRefusedPropositionReceivedBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countPendingPropositionReceivedBySheet(Sheet $sheet);
}
