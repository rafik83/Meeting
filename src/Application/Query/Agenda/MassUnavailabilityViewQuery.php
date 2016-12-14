<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class MassUnavailabilityViewQuery
{
    /**
     * @var Mass
     */
    public $mass;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Mass   $mass
     * @param string $locale
     */
    public function __construct(Mass $mass, $locale)
    {
        $this->mass   = $mass;
        $this->locale = $locale;
    }
}
