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
        'button-link'   => ObjectConstraint::class,
        'choice'        => ObjectConstraint::class,
        'collection'    => ObjectConstraint::class,
        'editable-text' => Object\EditableTextConstraint::class,
        'image'         => ObjectConstraint::class,
        'media'         => ObjectConstraint::class,
        'nomenclature'  => Object\NomenclatureConstraint::class,
        'participant'   => ObjectConstraint::class,
        'tag'           => ObjectConstraint::class,
        'text'          => ObjectConstraint::class,
        'carousel'      => ObjectConstraint::class,
        'telephone'     => Object\TelephoneConstraint::class,
        'country'       => Object\CountryConstraint::class,
        'url'           => Object\UrlConstraint::class,
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
