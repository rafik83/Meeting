<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class HappeningParticipation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Happening
     */
    private $happening;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * HappeningParticipation constructor.
     *
     * @param Happening   $happening
     * @param Participant $participant
     */
    public function __construct(Happening $happening, Participant $participant)
    {
        $this->happening   = $happening;
        $this->participant = $participant;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get happening.
     *
     * @return Happening
     */
    public function getHappening()
    {
        return $this->happening;
    }

    /**
     * Get participant.
     *
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }
}
