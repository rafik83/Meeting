<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Symfony\Component\Validator\Constraint;

class ObjectConstraint extends Constraint
{
    public $key;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return ObjectValidator::class;
    }

    public function getRequiredOptions()
    {
        return ['key'];
    }
}
