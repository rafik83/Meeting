<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Symfony\Component\Validator\Constraint;

class FormTemplateBlockStepDataConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return FormTemplateBlockStepDataValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
