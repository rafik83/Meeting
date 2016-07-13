<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Symfony\Component\Validator\ConstraintValidator;

abstract class ParticipantDataValidator extends ConstraintValidator
{
    protected $objectsConstraint = [
        'button-link'   => ObjectConstraint::class,
        'carousel'      => ObjectConstraint::class,
        'choice'        => ObjectConstraint::class,
        'country'       => Object\CountryConstraint::class,
        'collection'    => ObjectConstraint::class,
        'editable-text' => Object\EditableTextConstraint::class,
        'image'         => ObjectConstraint::class,
        'media'         => ObjectConstraint::class,
        'nomenclature'  => Object\NomenclatureConstraint::class,
        'participant'   => ObjectConstraint::class,
        'tag'           => ObjectConstraint::class,
        'telephone'     => Object\TelephoneConstraint::class,
        'text'          => ObjectConstraint::class,
        'url'           => Object\UrlConstraint::class,
        'gender'        => Object\GenderConstraint::class,
    ];
}
