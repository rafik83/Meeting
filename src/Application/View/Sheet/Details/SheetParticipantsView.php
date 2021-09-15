<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details;

class SheetParticipantsView
{
    /** @var OwnerView */
    public $owner;

    /** @var ParticipantView[] */
    public $participants;

    /**
     * SheetParticipantsView constructor.
     *
     * @param OwnerView         $owner
     * @param ParticipantView[] $participants
     */
    public function __construct(OwnerView $owner, array $participants)
    {
        $this->owner        = $owner;
        $this->participants = $participants;
    }
}
