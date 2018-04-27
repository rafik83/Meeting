<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Domain\Model\Happening\Speaker;

class SpeakerViewQuery
{
    /**
     * @var Speaker
     */
    public $speaker;

    /**
     * @param Speaker $speaker
     */
    public function __construct(Speaker $speaker)
    {
        $this->speaker = $speaker;
    }
}
