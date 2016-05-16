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
use Symfony\Component\Validator\Constraints;

class UrlValidator extends ObjectValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);

        if ($value instanceof Object\Url) {
            if (null !== $value->getData()) {
                $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.url')->validate($value->getContentValue(), new Constraints\Url());
            }
        } else {
            $this->context->buildViolation('validators.field.notValid.url')->atPath($constraint->key . '.url')->addViolation();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(Object $object, Constraint $constraint)
    {
        if ($object instanceof Object\Url && true === $object->getOption('required')) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.url')->validate($object->getContentValue(), new Constraints\NotBlank());
        }
    }
}
