<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Event;

use Symfony\Component\Validator\Constraint;

class UpdateInvoicePrefixConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return UpdateInvoicePrefixValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
