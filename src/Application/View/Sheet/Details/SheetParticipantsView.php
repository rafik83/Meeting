<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
