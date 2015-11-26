<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;

interface RequestRepositoryInterface
{
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
}
