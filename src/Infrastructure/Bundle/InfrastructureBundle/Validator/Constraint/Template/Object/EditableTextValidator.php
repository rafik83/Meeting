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
use Symfony\Component\Validator\Constraints\NotBlank;

class EditableTextValidator extends ObjectValidator
{
    /**
     * {@inheritdoc}
     */
    protected function checkRequired(Object $object, Constraint $constraint)
    {
        if (true === $object->getOption('required') && $object instanceof Object\EditableText) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.content')->validate($object->getContentValue(), new NotBlank());
        }
    }
}
