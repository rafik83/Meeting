<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;

class Cancel
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * Cancel constructor.
     *
     * @param Meeting $meeting
     */
    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }
}
