<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Symfony\Component\Validator\Constraint;

class PlanningQuantityConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return PlanningQuantityValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
