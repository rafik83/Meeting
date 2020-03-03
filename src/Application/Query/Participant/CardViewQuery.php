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

    /** @var bool */
    public $getCheckinStatus;

    public function __construct(
        Participant $participant,
        string $locale,
        bool $editable = false,
        $getCheckinStatus = false
    ) {
        $this->participant = $participant;
        $this->locale = $locale;
        $this->editable = $editable;
        $this->getCheckinStatus = $getCheckinStatus;
    }
}
