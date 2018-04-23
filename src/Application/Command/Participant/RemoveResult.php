<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

class RemoveResult
{
    /**
     * Array of participant's name
     * @var array
     */
    private $participants;

    /** @var bool */
    private $hasParticipantWithMeeting;

    /** @var bool */
    private $hasParticipantWithAttributedProduct;

    /**
     * @param array $participants
     * @param bool  $hasParticipantWithMeeting
     * @param bool  $hasParticipantWithAttributedProduct
     */
    public function __construct(
        array $participants = [],
        bool $hasParticipantWithMeeting = false,
        bool $hasParticipantWithAttributedProduct = false
    ) {
        $this->participants = $participants;
        $this->hasParticipantWithMeeting = $hasParticipantWithMeeting;
        $this->hasParticipantWithAttributedProduct = $hasParticipantWithAttributedProduct;
    }

    /**
     * @return bool
     */
    public function hasParticipantWithMeeting(): bool
    {
        return !empty($this->participants) && $this->hasParticipantWithMeeting;
    }

    /**
     * @return int
     */
    public function countParticipants(): int
    {
        return \count($this->participants);
    }

    /**
     * @return string
     */
    public function getParticipantsName(): string
    {
        return implode(', ', $this->participants);
    }
}
