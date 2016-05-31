<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\Object;

use Symfony\Component\Validator\Constraint;

class EditableTextConstraint extends Constraint
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var bool
     */
    public $isInBlock = true;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return EditableTextValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
