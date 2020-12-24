<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

class EventSubstitution implements SubstituteInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        return $sheet->getEvent()->getTitle();
    }
}
