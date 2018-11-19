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
use Symfony\Component\Validator\Constraints;

class DatetimeValidator extends TemplateObjectValidator
{
    public function validate($value, Constraint $constraint): void
    {
        parent::validate($value, $constraint);

        if ($value instanceof TemplateObject\DateTime) {
            $dateConstraint = $value->displayHours() ? new Constraints\DateTime() : new Constraints\Date();

            if (null !== $value->getData()) {
                $this->context
                    ->getValidator()
                    ->inContext($this->context)
                    ->atPath(sprintf('%s.date', $constraint->key))
                    ->validate($value->getContentValue(), $dateConstraint);
            }

            $this->checkDateInterval($value, $constraint);
        } else {
            $this->context
                ->buildViolation('validators.field.notValid.datetime')
                ->atPath(sprintf('%s.date', $constraint->key))
                ->addViolation();
        }
    }

    private function checkDateInterval(TemplateObject\DateTime $dateTime, Constraint $constraint): void
    {
        if ($dateTime->getDatetimeMin()) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('%s.date', $constraint->key))
                ->validate(
                    $dateTime->getContentValue(),
                    new Constraints\LessThanOrEqual($dateTime->getDatetimeMin())
                );
        }

        if ($dateTime->getDatetimeMax()) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('%s.date', $constraint->key))
                ->validate(
                    $dateTime->getContentValue(),
                    new Constraints\GreaterThanOrEqual($dateTime->getDatetimeMax())
                );
        }
    }

    protected function checkRequired(TemplateObject $object, Constraint $constraint): void
    {
        if ($object instanceof TemplateObject\DateTime && true === $object->getOption('required')) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('%s.date', $constraint->key))
                ->validate($object->getContentValue(), new Constraints\NotBlank());
        }
    }
}
