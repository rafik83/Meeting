<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details\Invoice;

class OrderView
{
    /** @var int */
    public $id;

    /** @var string */
    public $numero;

    /**
     * @param int    $id
     * @param string $numero
     */
    public function __construct($id, $numero)
    {
        $this->id     = $id;
        $this->numero = $numero;
    }
}
