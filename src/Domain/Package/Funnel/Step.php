<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Funnel;

class Step
{
    const TYPE_PLAN                 = 'plan';
    const TYPE_PARTICIPANT_PLANNING = 'participant_planning';
    const TYPE_OPTIONS              = 'options';

    /**
     * @var int
     */
    public $index;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $completed;

    /**
     * @var string
     */
    public $type;

    /**
     * @param int    $index
     * @param string $title
     * @param string $type
     * @param bool   $completed
     */
    public function __construct($index, $title, $type, $completed = false)
    {
        $this->index     = $index;
        $this->title     = $title;
        $this->type      = $type;
        $this->completed = $completed;
    }
}
