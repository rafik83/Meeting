<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

interface MassAssignmentRepositoryInterface
{
    /**
     * @param MassAssignment $massAssignment
     */
    public function add(MassAssignment $massAssignment);

    /**
     * @param Mass        $mass
     * @param Participant $participant
     *
     * @return MassAssignment|null
     */
    public function find(Mass $mass, Participant $participant);

    /**
     * @param Event $event
     *
     * @return MassAssignment[]
     */
    public function findByEvent(Event $event);
}
