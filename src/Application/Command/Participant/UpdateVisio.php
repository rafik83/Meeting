<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class UpdateVisio
{
    /** @var bool */
    public $visio;

    /** @var Participant */
    public $participant;

    /**
     * VisioHandler constructor.
     *
     * @param Participant $participant
     * @param bool        $visio
     */
    public function __construct(
        Participant $participant,
        $visio
    ) {
        $this->participant = $participant;
        $this->visio = $visio;
    }
}
