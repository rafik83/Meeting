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

class UpdateParticipantProductQuantity
{
    /** @var Sheet */
    public $sheet;

    /** @var Participant */
    public $participant;

    /** @var int */
    public $productId;

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param int         $productId
     */
    public function __construct(
        Sheet $sheet,
        Participant $participant,
        int $productId
    ) {
        $this->sheet = $sheet;
        $this->participant = $participant;
        $this->productId = $productId;
    }
}
