<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareChangeNewMailAccountView;

class CurrentEmailSubstitution implements SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if ($prepareMail instanceof PrepareChangeNewMailAccountView){
            return $prepareMail->user->getEmail();
        }

        return '';
    }
}
