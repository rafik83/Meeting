<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Model\Product;

class SelectParticipantAndPlanning extends AbstractStep
{
    /** @var int */
    public $planningQuantity = 0;

    /** @var array of participantId => Product */
    public $participantsProduct = [];

    public function __get(int $participantId): ?Product
    {
        return $this->participantsProduct[$participantId];
    }

    public function __set(int $participantId, ?Product $participantProduct = null)
    {
        $this->participantsProduct[$participantId] = $participantProduct;
    }
}
