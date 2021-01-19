<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Symfony\Component\Validator\Constraint;

class TemplateObjectConstraint extends Constraint
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
        return TemplateObjectValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return ['key'];
    }
}
