<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Domain\Template\TemplateData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CompanyDataValidator extends ConstraintValidator
{
    private $objectsConstraint = [
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
    ];

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof TemplateData) {
            $this->context->buildViolation('A TemplateData is expected')->addViolation();
        }

        $validator = $this->context->getValidator()->inContext($this->context);

        foreach ($value->getCompanyObjects() as $key => $object) {
            $class      = $this->objectsConstraint[$object->getType()];
            $constraint = new $class(['key' => $key]);
            $validator->validate($object, $constraint, ['company', 'Default']);
        }
    }
}
