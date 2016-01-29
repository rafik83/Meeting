<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Model\Participant;

class Add
{
    /**
     * @var Participant[]
     */
    public $participants = [];

    /**
     * @var \DateTime
     */
    public $from;

    /**
     * @var \DateTime
     */
    public $to;

    /**
     * AddUnavailability constructor.
     */
    public function __construct()
    {
        $this->from = new \DateTime();
        $this->to   = new \DateTime();
    }
}
