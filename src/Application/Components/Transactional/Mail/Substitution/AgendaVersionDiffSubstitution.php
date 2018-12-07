<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareVersionDiffChangedMailView;
use Proximum\Vimeet\Application\Components\User\Agenda\Version\VersionDiffVerbalizedGetter;

class AgendaVersionDiffSubstitution implements SubstituteInterface
{
    /** @var VersionDiffVerbalizedGetter */
    private $versionDiffVerbalizedGetter;

    public function __construct(
        VersionDiffVerbalizedGetter $versionDiffVerbalizedGetter
    ) {
        $this->versionDiffVerbalizedGetter = $versionDiffVerbalizedGetter;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if ($prepareMail instanceof PrepareVersionDiffChangedMailView) {
            return $prepareMail->getAgendaModifications();
        }

        return $this->versionDiffVerbalizedGetter->getVerbalizedDiff($prepareMail->event, $prepareMail->user);
    }
}
