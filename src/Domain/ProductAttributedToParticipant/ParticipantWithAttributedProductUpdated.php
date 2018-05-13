<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Model\Participant;

/**
 * Spool of participants which have a attributed product updated (added or removed)
 */
class ParticipantWithAttributedProductUpdated
{
    /** @var Participant[] */
    private $participantWithAttributedProductUpdated = [];

    public function add(Participant $participant): void
    {
        $this->participantWithAttributedProductUpdated[$participant->getId()] = $participant;
    }

    /**
     * @param Participant[] $participants
     *
     * @return Participant[]
     */
    public function getFilteredByParticipants(array $participants): array
    {
        $filteredParticipants = [];

        foreach ($participants as $participant) {
            if (isset($this->participantWithAttributedProductUpdated[$participant->getId()])) {
                $filteredParticipants[] = $participant;
            }
        }

        return $filteredParticipants;
    }
}
