<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareChangeOldMailAccountView;

class NewMailSubstitution implements SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if ($prepareMail instanceof PrepareChangeOldMailAccountView) {
            return $prepareMail->changeMailToken->getMail();
        }

        return '';
    }
}
