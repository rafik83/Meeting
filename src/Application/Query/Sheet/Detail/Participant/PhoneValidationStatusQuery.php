<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class PhoneValidationStatusQuery
{
    /** @var Participant */
    public $participant;

    /**
     * @param Participant $participant
     */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }
}
