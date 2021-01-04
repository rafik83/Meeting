<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Symfony\Component\Validator\Constraint;

class EditableTextConstraint extends Constraint
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var bool
     */
    public $isInBlock = true;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return EditableTextValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->isInBlock ? $this->key . '.content' : 'content';
    }
}
