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

    /** @var ParticipantProductView[] */
    public $participantProductViews;

    /**
     * @param ParticipantView[]        $participantViews
     * @param ParticipantProductView[] $participantProductViews
     */
    public function __construct(array $participantViews, array $participantProductViews)
    {
        $this->participantViews = $participantViews;
        $this->participantProductViews = $participantProductViews;
    }
}
