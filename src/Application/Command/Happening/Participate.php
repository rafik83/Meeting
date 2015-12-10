<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;

class Participate
{
    /**
     * @var Happening
     */
    public $happening;

    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * Participate constructor.
     *
     * @param Happening $happening
     * @param array     $participants
     */
    public function __construct(Happening $happening, array $participants)
    {
        $this->happening    = $happening;
        $this->participants = $participants;
    }
}
