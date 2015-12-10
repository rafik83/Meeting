<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Model\Unavailability;

class Update
{
    /**
     * @var Unavailability
     */
    public $unavailability;

    /**
     * @var \DateTime
     */
    public $from;

    /**
     * @var \DateTime
     */
    public $to;

    /**
     * Update constructor.
     *
     * @param Unavailability $unavailability
     */
    public function __construct(Unavailability $unavailability)
    {
        $this->unavailability = $unavailability;
        $this->from = $unavailability->getBegin();
        $this->to = $unavailability->getEnd();
    }
}
