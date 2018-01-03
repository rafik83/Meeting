<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening\Participant;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantsAllowedToAccessQuery
{
    /** @var Happening */
    public $happening;

    /** @var Participant[] */
    public $participants;

    /**
     * @param Happening     $happening
     * @param Participant[] $participants
     */
    public function __construct(Happening $happening, array $participants)
    {
        $this->happening = $happening;
        $this->participants = $participants;
    }
}
