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
    /**
     * @var int
     */
    public $planningQuantity = 0;

    /**
     * @var array of participantId => Product
     */
    public $participantsProduct = [];

    /**
     * @param int $participantId
     *
     * @return int Id of participant product
     */
    public function __get(int $participantId)
    {
        return $this->participantsProduct[$participantId];
    }

    /**
     * @param int     $participantId
     * @param Product $participantProduct
     */
    public function __set(int $participantId, Product $participantProduct)
    {
        $this->participantsProduct[$participantId] = $participantProduct;
    }
}
