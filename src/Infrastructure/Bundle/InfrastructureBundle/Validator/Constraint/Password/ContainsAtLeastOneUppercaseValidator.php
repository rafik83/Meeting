<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Password;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsAtLeastOneUppercaseValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!preg_match("~[A-Z]~", $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
