<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Symfony\Component\Validator\ConstraintValidator;

abstract class ParticipantDataValidator extends ConstraintValidator
{
    protected $objectsConstraint = [
        'boolean'       => TemplateObject\BooleanObjectConstraint::class,
        'button-link'   => TemplateObjectConstraint::class,
        'carousel'      => TemplateObjectConstraint::class,
        'choice'        => TemplateObjectConstraint::class,
        'country'       => TemplateObject\CountryConstraint::class,
        'collection'    => TemplateObjectConstraint::class,
        'editable-text' => TemplateObject\EditableTextConstraint::class,
        'gender'        => TemplateObject\GenderConstraint::class,
        'image'         => TemplateObject\ImageConstraint::class,
        'media'         => TemplateObjectConstraint::class,
        'nomenclature'  => TemplateObject\NomenclatureConstraint::class,
        'participant'   => TemplateObjectConstraint::class,
        'tag'           => TemplateObjectConstraint::class,
        'telephone'     => TemplateObject\TelephoneConstraint::class,
        'text'          => TemplateObjectConstraint::class,
        'url'           => TemplateObject\UrlConstraint::class,
    ];
}
