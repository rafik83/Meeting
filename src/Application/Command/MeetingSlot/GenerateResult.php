<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

class GenerateResult
{
    /**
     * @var int
     */
    public $count;

    /**
     * GenerateResult constructor.
     *
     * @param int $count
     */
    public function __construct($count)
    {
        $this->count = $count;
    }
}
