<?php

namespace Proximum\Vimeet\Application\View\Participant\Sheet;

class ParticipantListView
{
    /** @var ParticipantView */
    public $currentParticipant;

    /** @var ParticipantView[] */
    public $participants;

    public function __construct(ParticipantView $currentParticipant, array $participants)
    {
        $this->currentParticipant = $currentParticipant;
        $this->participants = $participants;
    }

    public function hasMoreThanOneParticipant(): bool
    {
        return \count($this->participants) > 1;
    }
}
