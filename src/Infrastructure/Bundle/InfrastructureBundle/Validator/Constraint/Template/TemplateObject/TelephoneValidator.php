<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class TelephoneValidator extends TemplateObjectValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);

        if ($value instanceof TemplateObject\Telephone) {
            $validator = $this->context->getValidator()->inContext($this->context);
            $validator->atPath($constraint->key . '.telephone')->validate($value->getContentValue(), new Regex([
                'pattern' => '#^\+(?!(?:\d*-){5,})(?!(?:\d* ){5,})[\d\- /.]+$#',
                'message' => 'validators.field.notValid.telephone',
            ]));
        } else {
            $this->context->buildViolation('validators.field.notValid.telephone')->atPath($constraint->key . '.telephone')->addViolation();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\Telephone && true === $object->getOption('required')) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.telephone')->validate($object->getContentValue(), new NotBlank());
        }
    }
}
