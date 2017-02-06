<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class UpdateVisio
{
    /**
     * @var boolean
     */
    public $visio;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * VisioHandler constructor.
     *
     * @param Participant   $participant
     * @param boolean       $visio
     */
    public function __construct(
        Participant $participant,
        $visio
    ) {
        $this->participant = $participant;
        $this->visio = $visio;
    }
}
