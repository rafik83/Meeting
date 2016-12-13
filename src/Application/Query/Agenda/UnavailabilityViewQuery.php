<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Unavailability;

class UnavailabilityViewQuery
{
    /**
     * @var Unavailability
     */
    public $unavailability;

    /**
     * @var string
     */
    public $key;

    /**
     * @param Unavailability $unavailability
     * @param string         $key
     */
    public function __construct(Unavailability $unavailability, $key)
    {
        $this->unavailability = $unavailability;
        $this->key            = $key;
    }
}
