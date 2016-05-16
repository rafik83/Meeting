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
                $this->context->getValidator()->inContext($this->context)->atPath($constraint->key)->validate($value, new Constraints\Url());
            }
        } else {
            $this->context->buildViolation('validators.field.notValid.url')->atPath($constraint->key)->addViolation();
        }
    }
}
