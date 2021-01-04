<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Symfony\Component\Validator\Constraint;

class DatetimeConstraint extends Constraint
{
    /** @var string */
    public $key;

    public function validatedBy(): string
    {
        return DatetimeValidator::class;
    }
}
