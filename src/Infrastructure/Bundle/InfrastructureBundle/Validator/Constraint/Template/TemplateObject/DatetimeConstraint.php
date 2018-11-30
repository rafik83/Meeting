<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
