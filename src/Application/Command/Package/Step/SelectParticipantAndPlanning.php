<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

class SelectParticipantAndPlanning extends AbstractStep
{
    /**
     * @var int
     */
    public $planningQuantity = 0;

    /**
     * @var array of participantId => participantProductId
     */
    public $participantsProduct;

    /**
     * @param int $participantId
     *
     * @return int Id of participant product
     */
    public function __get($participantId)
    {
        return $this->participantsProduct[$participantId];
    }

    /**
     * @param int $participantId
     * @param int $participantProductId
     */
    public function __set($participantId, $participantProductId)
    {
        $this->participantsProduct[$participantId] = $participantProductId;
    }
}
