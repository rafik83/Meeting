<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Spot\Agenda;

class SpotView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var bool
     */
    public $isVisio;

    /**
     * SpotView constructor.
     *
     * @param int    $id
     * @param string $reference
     * @param bool   $visio
     */
    public function __construct($id, $reference, $visio)
    {
        $this->id        = $id;
        $this->reference = $reference;
        $this->isVisio   = $visio;
    }
}
