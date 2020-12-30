<?php

namespace Proximum\Vimeet\Domain\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Model\Participant;

/**
 * Spool of participants which have an attributed product updated (added or removed)
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
