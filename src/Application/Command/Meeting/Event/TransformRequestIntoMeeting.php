<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class TransformRequestIntoMeeting
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var bool
     */
    public $visio;

    /**
     * TransformRequestIntoMeeting constructor.
     *
     * @param Request $request
     * @param bool    $visio
     */
    public function __construct(Request $request, bool $visio = false)
    {
        $this->request = $request;
        $this->event   = $request->getEvent();
        $this->visio   = $visio;
    }
}
