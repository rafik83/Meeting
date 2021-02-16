<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

class TypeTitleSubstitution implements SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail->hasSheet()) {
            return '';
        }

        $locale = $prepareMail->event->getAvailableLocale($prepareMail->locale);

        return $prepareMail->sheet->getTypeTitle($locale);
    }
}
