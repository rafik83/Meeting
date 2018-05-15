<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Sheet\Filter;

use Symfony\Component\Validator\Constraint;

class ReminderDateChoiceConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return ReminderDateChoiceValidator::class;
    }
}
