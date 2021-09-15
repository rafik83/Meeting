<?php

namespace Proximum\Vimeet\Application\Components\Participant\Remove;

final class ConflictsView
{
    /** @var ParticipantConflictView[] */
    public $participantConflicts;

    public function __construct()
    {
        $this->participantConflicts = [];
    }

    public function hasConflict(): bool
    {
        return !empty($this->participantConflicts);
    }

    public function addConflict(ParticipantConflictView $participantConflictView): void
    {
        $this->participantConflicts[$participantConflictView->participantId] = $participantConflictView;
    }
}
