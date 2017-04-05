<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Invoice\Numero;

class InvoiceNumeroView
{
    /** @var int */
    public $increment;

    /** @var string */
    public $prefix;

    /** @var int */
    public $year;

    /**
     * @param string $prefix
     * @param int    $year
     * @param int    $increment
     */
    public function __construct($prefix, $year, $increment)
    {
        $this->prefix    = $prefix;
        $this->year      = $year;
        $this->increment = $increment;
    }
}
