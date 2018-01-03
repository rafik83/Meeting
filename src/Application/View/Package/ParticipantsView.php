<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class ParticipantsView
{
    /** @var ParticipantView[] */
    public $participantViews;

    /**
     * @param ParticipantView[] $participantViews
     */
    public function __construct(array $participantViews)
    {
        $this->participantViews = $participantViews;
    }
}
