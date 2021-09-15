<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

class OwnerFirstnameSubstitution implements SubstituteInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        return $sheet->getOwner()->getFirstName();
    }
}
