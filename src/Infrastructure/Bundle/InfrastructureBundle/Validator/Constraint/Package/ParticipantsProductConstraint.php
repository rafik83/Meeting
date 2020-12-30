<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Symfony\Component\Validator\Constraint;

class ParticipantsProductConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return ParticipantsProductValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
