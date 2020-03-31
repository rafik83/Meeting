<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Password;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsAtLeastOneLowercaseValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match("~[a-z]~", $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
