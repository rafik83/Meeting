<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class SpotView
{
    /** @var int */
    public $id;

    /** @var string */
    public $label;

    /**
     * @param int    $id
     * @param string $label
     */
    public function __construct($id, $label)
    {
        $this->id    = $id;
        $this->label = $label;
    }
}
