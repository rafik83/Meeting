<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class CardViewQuery
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var bool
     */
    public $editable;

    /**
     * @param Participant $participant
     * @param string      $locale
     * @param bool        $editable
     */
    public function __construct(Participant $participant, $locale, $editable = false)
    {
        $this->participant = $participant;
        $this->locale      = $locale;
        $this->editable    = $editable;
    }
}
