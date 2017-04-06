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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Participate
{
    /** @var Happening */
    public $happening;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $createdBy;

    /** @var Participant[] */
    public $participants;

    /** @var null|string */
    public $question;

    /**
     * @param Happening   $happening
     * @param Sheet       $sheet
     * @param User        $createdBy
     * @param array       $participants
     * @param null|string $question
     */
    public function __construct(
        Happening $happening,
        Sheet $sheet,
        User $createdBy,
        array $participants,
        $question = null
    ) {
        $this->happening    = $happening;
        $this->sheet        = $sheet;
        $this->createdBy    = $createdBy;
        $this->participants = $participants;
        $this->question     = $question;
    }
}
