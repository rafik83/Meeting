<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planner\Result;

class IdResult
{
    /** @var int */
    public $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = intval($id);
    }
}
