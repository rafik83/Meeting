<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Participant\Remove;

class ParticipantConflictView
{
    /** @var int */
    public $participantId;

    /** @var string */
    public $participantName;

    /** @var string */
    public $productTitle;

    public function __construct(
        int $participantId,
        $participantName,
        $productTitle
    ) {
        $this->participantId = $participantId;
        $this->participantName = $participantName;
        $this->productTitle = $productTitle;
    }
}
