<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\Object;

use Proximum\Vimeet\Domain\Template\Object;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\ObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class NomenclatureValidator extends ObjectValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof Object\Nomenclature) {
            $this->checkRequired($value, $constraint);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(Object $object, Constraint $constraint)
    {
        if ($object instanceof Object\Nomenclature && true === $object->getOption('required')) {
            if ($object->isSingles()) {
                if ($object->getNomenclatureModel() !== null) {
                    $depth = $object->getNomenclatureModel()->getDepth();
                    $reference = [
                        1 => 'first',
                        2 => 'second',
                        3 => 'third',
                    ];

                    $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.item.' . $reference[$depth])->validate($object->getItems(), new Constraints\NotBlank());
                }
            } elseif ($object->isRadios()) {
                $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.item')->validate($object->getItems(), new Constraints\NotBlank());
            } elseif ($object->isCheckboxes()) {
                $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.items')->validate($object->getItems(), new Constraints\NotBlank());
            }
        }
    }
}
