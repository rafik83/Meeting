<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\Object;

use Proximum\Vimeet\Domain\Template\Object;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\ObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditableTextValidator extends ObjectValidator
{
    /**
     * @param string                  $path
     * @param mixed                   $value
     * @param Constraint|Constraint[] $constraints
     * @param array|null              $groups
     */
    private function validateAt($path, $value, $constraints = null, $groups = null)
    {
        $this->context->getValidator()->inContext($this->context)->atPath($path)->validate($value, $constraints, $groups);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(Object $object, Constraint $constraint)
    {
        if ($constraint instanceof EditableTextConstraint && $object instanceof Object\EditableText && true === $object->isRequired()) {
            $this->validateAt($constraint->getPath(), $object->getContentValue(), new NotBlank());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkMinLength(Object $object, Constraint $constraint)
    {
        if ($constraint instanceof EditableTextConstraint && $object instanceof Object\EditableText && $object->hasMinLength()) {
            $this->validateAt($constraint->getPath(), $object->getContentValue(), new Length([
                'min' => $object->getMinLength(),
            ]));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkMaxLength(Object $object, Constraint $constraint)
    {
        if ($constraint instanceof EditableTextConstraint && $object instanceof Object\EditableText && $object->hasMaxLength()) {
            $this->validateAt($constraint->getPath(), $object->getContentValue(), new Length([
                'max' => $object->getMaxLength(),
            ]));
        }
    }
}
