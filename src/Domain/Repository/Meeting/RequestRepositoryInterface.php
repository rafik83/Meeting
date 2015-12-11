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
use Proximum\Vimeet\Domain\Model\Sheet;

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
     * @return mixed
     */
    public function getPendingByEvent(Event $event, $page, $limit);
}
