<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareParticipantAddedMailView;

class GuestEmailSubstitution implements SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail instanceof PrepareParticipantAddedMailView) {
            return '';
        }

        return $prepareMail->guest->getEmail();
    }
}
