<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Spot;

class SpotReferenceSubstitution implements SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail->hasSheet()) {
            return '';
        }

        $spot = $prepareMail->sheet->getSpot();

        if (!$spot instanceof Spot) {
            return '';
        }

        return $spot->getReference();
    }
}
