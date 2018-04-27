<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Domain\Model\Happening\Speaker;

class Delete
{
    /**
     * @var Speaker
     */
    public $speaker;

    /**
     * Delete constructor.
     *
     * @param Speaker $speaker
     */
    public function __construct(Speaker $speaker)
    {
        $this->speaker = $speaker;
    }
}
