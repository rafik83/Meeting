<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;

class MeetingEvaluationSubstitution implements SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail instanceof PrepareMeetingFollowUpView) {
            return '';
        }

        return $prepareMail->evaluation;
    }
}
