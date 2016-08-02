<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Domain\Template\Block;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BlockValidator extends ConstraintValidator
{
    private $objects = [
        'button-link'   => TemplateObjectConstraint::class,
        'choice'        => TemplateObjectConstraint::class,
        'collection'    => TemplateObjectConstraint::class,
        'editable-text' => TemplateObject\EditableTextConstraint::class,
        'image'         => TemplateObjectConstraint::class,
        'media'         => TemplateObjectConstraint::class,
        'nomenclature'  => TemplateObject\NomenclatureConstraint::class,
        'participant'   => TemplateObjectConstraint::class,
        'tag'           => TemplateObjectConstraint::class,
        'text'          => TemplateObjectConstraint::class,
        'carousel'      => TemplateObjectConstraint::class,
        'telephone'     => TemplateObject\TelephoneConstraint::class,
        'country'       => TemplateObject\CountryConstraint::class,
        'url'           => TemplateObject\UrlConstraint::class,
        'gender'        => TemplateObject\GenderConstraint::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Block) {
            $this->context->buildViolation('Block expected')->addViolation();
        }

        $validator = $this->context->getValidator()->inContext($this->context);

        foreach ($value->getEditableObjects() as $key => $object) {
            $class      = $this->objects[$object->getType()];
            $constraint = new $class(['key' => $key]);
            $validator->validate($object, $constraint, ['block', 'Default']);
        }
    }
}
