<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Symfony\Component\Validator\Constraint;

class TelephoneConstraint extends Constraint
{
    /**
     * @var string
     */
    public $key;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return TelephoneValidator::class;
    }
}
