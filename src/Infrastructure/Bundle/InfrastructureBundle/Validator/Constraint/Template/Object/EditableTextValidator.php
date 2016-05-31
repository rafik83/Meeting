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
     * {@inheritdoc}
     */
    protected function checkRequired(Object $object, Constraint $constraint)
    {
        if ($object instanceof Object\EditableText && true === $object->getOption('required')) {
            if ($constraint->isInBlock) {
                $this->context->getValidator()->inContext($this->context)->atPath(
                    $constraint->key.'.content'
                )->validate($object->getContentValue(), new NotBlank());
            } else {
                $this->context->getValidator()
                    ->inContext($this->context)
                    ->atPath('content')
                    ->validate($object->getContentValue(), new NotBlank());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkMinLength(Object $object, Constraint $constraint)
    {
        if ($object instanceof Object\EditableText && null !== $object->getOption('minLength')) {
            if ($constraint->isInBlock) {
                $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.content')->validate($object->getContentValue(), new Length(['min' => $object->getOption('minLength')]));
            } else {
                $this->context->getValidator()->inContext($this->context)->atPath('content')->validate($object->getContentValue(), new Length(['min' => $object->getOption('minLength')]));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkMaxLength(Object $object, Constraint $constraint)
    {
        if ($object instanceof Object\EditableText && null !== $object->getOption('maxLength')) {
            if ($constraint->isInBlock) {
                $this->context->getValidator()->inContext($this->context)->atPath(
                    $constraint->key.'.content'
                )->validate($object->getContentValue(), new Length(['max' => $object->getOption('maxLength')]));
            } else {
                $this->context->getValidator()
                    ->inContext($this->context)
                    ->atPath('content')
                    ->validate($object->getContentValue(), new Length(['max' => $object->getOption('maxLength')]));
            }
        }
    }
}
