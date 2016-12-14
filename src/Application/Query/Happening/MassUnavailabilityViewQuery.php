<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

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
     * @var string
     */
    public $key;

    /**
     * @param Mass   $mass
     * @param string $locale
     * @param string $key
     */
    public function __construct(Mass $mass, $locale, $key)
    {
        $this->mass   = $mass;
        $this->locale = $locale;
        $this->key    = $key;
    }
}
