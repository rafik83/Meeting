<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin\Request;

use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;

class MeetingRequestListView
{
    /**
     * @var int
     */
    public $sheetId;

    /**
     * @var RequestView[]
     */
    public $requests;

    /**
     * @param int           $sheetId
     * @param RequestView[] $requests
     */
    public function __construct($sheetId, array $requests)
    {
        $this->sheetId  = $sheetId;
        $this->requests = $requests;
    }
}
