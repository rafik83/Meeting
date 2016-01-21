<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Meeting;

class ShowDetailsView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $sheetName;

    /**
     * @var array
     */
    public $participantNames = [];

    /**
     * @var array
     */
    public $messages = [];

    /**
     * @var string
     */
    public $state;

    public function __construct($id, $sheetName, $participantNames, $messages, $state)
    {
        $this->id               = $id;
        $this->sheetName        = $sheetName;
        $this->participantNames = $participantNames;
        $this->messages         = $messages;
        $this->state            = $state;
    }
}
