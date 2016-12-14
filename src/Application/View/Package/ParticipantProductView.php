<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class ParticipantProductView
{
    /** @var string */
    public $title;

    /** @var float */
    public $unitPrice;

    /** @var string */
    public $currency;

    /** @var string */
    public $vatMode;

    /** @var bool */
    public $isIncluded;

    /**
     * @param string $title
     * @param float  $unitPrice
     * @param string $currency
     * @param string $vatMode
     * @param bool   $isIncluded
     */
    public function __construct($title, $unitPrice, $currency, $vatMode, $isIncluded)
    {
        $this->title      = $title;
        $this->unitPrice  = $unitPrice;
        $this->currency   = $currency;
        $this->vatMode    = $vatMode;
        $this->isIncluded = $isIncluded;
    }
}
