<?php

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
            $constraints = [
                $value->displayHours() ? new Constraints\DateTime() : new Constraints\Date(),
            ];

            if ($value->getOptionDate('datetime_min')) {
                $constraints[] = new Constraints\GreaterThanOrEqual($value->getOptionDate('datetime_min'));
            }

            if ($value->getOptionDate('datetime_max')) {
                $constraints[] = new Constraints\LessThanOrEqual($value->getOptionDate('datetime_max'));
            }

            if (null !== $value->getData()) {
                foreach ($constraints as $dateConstraint) {
                    $this->context
                        ->getValidator()
                        ->inContext($this->context)
                        ->atPath(sprintf('%s.datetime', $constraint->key))
                        ->validate($value->getDatetime(), $dateConstraint);
                }
            }
        } else {
            $this->context
                ->buildViolation('validators.field.notValid.datetime')
                ->atPath(sprintf('%s.datetime', $constraint->key))
                ->addViolation();
        }
    }

    protected function checkRequired(TemplateObject $object, Constraint $constraint): void
    {
        if ($object instanceof TemplateObject\DateTime && true === $object->getOption('required')) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('%s.datetime', $constraint->key))
                ->validate($object->getContentValue(), new Constraints\NotBlank());
        }
    }
}
