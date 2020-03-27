<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Password;

use Symfony\Component\Validator\Constraint;

class ContainsAtLeastOneUppercase extends Constraint
{
    public $message = 'validators.password.atLeastOneUppercase';
}
