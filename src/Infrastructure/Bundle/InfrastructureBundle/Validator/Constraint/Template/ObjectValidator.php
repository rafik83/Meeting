<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Domain\Template\Object;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;

class ObjectValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof Object) {
            $this->checkRequired($value, $constraint);
            $this->checkMinLength($value, $constraint);
            $this->checkMaxLength($value, $constraint);
        }
    }

    /**
     * @param Object     $object
     * @param Constraint $constraint
     */
    protected function checkRequired(Object $object, Constraint $constraint)
    {
        if (true === $object->getOption('required')) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key)->validate($object->getData(), new NotBlank());
        }
    }

    /**
     * @param Object     $object
     * @param Constraint $constraint
     */
    protected function checkMinLength(Object $object, Constraint $constraint)
    {
        if (null !== $object->getOption('minLength')) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key)->validate($object->getData(), new Length(['min' => $object->getOption('minLength')]));
        }
    }

    /**
     * @param Object     $object
     * @param Constraint $constraint
     */
    protected function checkMaxLength(Object $object, Constraint $constraint)
    {
        if (null !== $object->getOption('maxLength')) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key)->validate($object->getData(), new Length(['max' => $object->getOption('maxLength')]));
        }
    }
}
