<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\Object;

use Proximum\Vimeet\Domain\Template\Object\Telephone;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\ObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Regex;

class TelephoneValidator extends ObjectValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);

        if ($value instanceof Telephone) {
            $validator = $this->context->getValidator()->inContext($this->context);
            $validator->atPath($constraint->key)->validate($value->getContentValue(), new Regex([
                'pattern' => '#^(?!(?:\d*-){5,})(?!(?:\d* ){5,})\+?[\d- /.]+$#',
                'message' => 'validators.field.notValid.telephone',
            ]));
        } else {
            $this->context->buildViolation('validators.field.notValid.telephone')->atPath($constraint->key)->addViolation();
        }
    }
}
