<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;

interface RouterInterface
{
    /**
     * @param Sheet   $sheet
     * @param Request $request
     *
     * @return string
     */
    public function generateMeetingRequest(Sheet $sheet, Request $request);
}
