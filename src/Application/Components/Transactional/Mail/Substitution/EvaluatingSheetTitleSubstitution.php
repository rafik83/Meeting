<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;

class EvaluatingSheetTitleSubstitution implements SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail instanceof PrepareMeetingFollowUpView) {
            return '';
        }

        return $prepareMail->evaluatingSheetTitle;
    }
}
