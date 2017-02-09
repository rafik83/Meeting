<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Meeting\Request;

class RequestSheetsViewQuery
{
    /**
     * @var Request
     */
    public $meetingRequest;

    /**
     * @var string
     */
    public $locale;

    /**
     * RequestParticipantViewQuery constructor.
     *
     * @param Request $meetingRequest
     * @param string  $locale
     */
    public function __construct(Request $meetingRequest, $locale)
    {
        $this->meetingRequest = $meetingRequest;
        $this->locale         = $locale;
    }
}

