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
    /** @var Happening */
    public $happening;

    /** @var Participant[] */
    public $participants;

    /** @var string */
    public $question;

    /**
     * @param Happening $happening
     * @param array     $participants
     * @param string    $question
     */
    public function __construct(Happening $happening, array $participants, $question = '')
    {
        $this->happening    = $happening;
        $this->participants = $participants;
        $this->question     = $question;
    }
}
