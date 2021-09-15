<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Event;

use Symfony\Component\Validator\Constraint;

class SearchFacetConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return SearchFacetValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
