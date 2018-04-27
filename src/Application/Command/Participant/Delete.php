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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Delete
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var User
     */
    public $requester;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @param Sheet       $sheet
     * @param User        $requester
     * @param Participant $participant
     */
    public function __construct(Sheet $sheet, User $requester, Participant $participant)
    {
        $this->sheet       = $sheet;
        $this->requester   = $requester;
        $this->participant = $participant;
    }
}
