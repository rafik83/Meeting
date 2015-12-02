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

class Unparticipate
{
    /**
     * @var Happening
     */
    public $happening;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * Participate constructor.
     *
     * @param Happening   $happening
     * @param Participant $participant
     */
    public function __construct(Happening $happening, Participant $participant)
    {
        $this->happening   = $happening;
        $this->participant = $participant;
    }
}
