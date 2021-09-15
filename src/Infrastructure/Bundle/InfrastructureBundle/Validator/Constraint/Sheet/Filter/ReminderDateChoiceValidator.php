<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Sheet\Filter;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReminderDateChoiceValidator extends ConstraintValidator
{
    private const BEGIN = 'begin';
    private const END = 'end';

    /**
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint): void
    {
        $this->checkBothValueSet($value);
        $this->checkBeginBeforeEnd($value);
    }

    private function checkBothValueSet($value): void
    {
        if (!array_key_exists(self::BEGIN, $value) && !array_key_exists(self::END, $value)) {
            return;
        }

        if ((null !== $value['begin'] && null !== $value['end'])
            || (null === $value['begin'] && null === $value['end'])
        ) {
            return;
        }

        $this
            ->context
            ->buildViolation('validators.admin.sheet.filter.reminderDate.beginAndEndMustBeSet')
            ->atPath('[begin]')
            ->addViolation()
        ;
    }

    private function checkBeginBeforeEnd($value): void
    {
        if (!array_key_exists(self::BEGIN, $value) && !array_key_exists(self::END, $value)) {
            return;
        }

        if ($value['begin'] instanceof \DateTimeInterface
            && $value['end'] instanceof \DateTimeInterface
            && $value['begin'] > $value['end']
        ) {
            $this
                ->context
                ->buildViolation('validators.admin.sheet.filter.reminderDate.beginMustBeBeforeEnd')
                ->atPath('[begin]')
                ->addViolation()
            ;
        }
    }
}
