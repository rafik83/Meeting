<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Meetings;

class SheetMeetingsListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $requestsNumber;

    /**
     * @var int
     */
    public $meetingsNumber;

    /**
     * @var float
     */
    public $requestsTransformation;

    /**
     * @param int    $id
     * @param string $title
     * @param string $type
     * @param int    $requestsNumber
     * @param int    $meetingsNumber
     * @param float  $requestsTransformation
     */
    public function __construct($id, $title, $type, $requestsNumber, $meetingsNumber, $requestsTransformation)
    {
        $this->id                    = $id;
        $this->title                 = $title;
        $this->type                  = $type;
        $this->requestsNumber        = $requestsNumber;
        $this->meetingsNumber        = $meetingsNumber;
        $this->requestsTranformation = $requestsTransformation;
    }
}
