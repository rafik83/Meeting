<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Password;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsAtLeastOneNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!preg_match("~[0-9]~", $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
