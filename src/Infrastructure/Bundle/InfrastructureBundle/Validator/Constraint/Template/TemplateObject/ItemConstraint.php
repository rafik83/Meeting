<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Symfony\Component\Validator\Constraint;

class ItemConstraint extends Constraint
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
        return ItemValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
