<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Password;

use Symfony\Component\Validator\Constraint;

class ContainsAtLeastOneNumber extends Constraint
{
    public $message = 'validators.password.atLeastOneNumber';
}
