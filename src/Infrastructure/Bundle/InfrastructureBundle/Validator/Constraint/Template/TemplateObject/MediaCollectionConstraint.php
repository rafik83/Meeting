<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Symfony\Component\Validator\Constraint;

class MediaCollectionConstraint extends Constraint
{
    /**
     * @var string
     */
    public $key;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return MediaCollectionValidator::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
