<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
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
    public $active;

    /**
     * @var bool
     */
    public $visio;

    /**
     * SpotView constructor.
     *
     * @param int    $id
     * @param string $reference
     * @param bool   $active
     * @param bool   $visio
     */
    public function __construct($id, $reference, $active, $visio)
    {
        $this->id        = $id;
        $this->reference = $reference;
        $this->active    = $active;
        $this->visio     = $visio;
    }
}
