<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\Object;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintValidator;

class ItemValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        foreach ($value->getCollection()->getItems() as $item) {
            if (null !== $item->getterTitle()) {
                if (is_array($item->getterTitle())) {
                    $this->validateArray($item->getterTitle(), $constraint);
                } else {
                    $this->validateString($item->getterTitle(), $constraint);
                }
            }
        }
    }

    /**
     * @param array      $titles
     * @param Constraint $constraint
     */
    public function validateArray(array $titles, Constraint $constraint)
    {
        foreach ($titles as $locale => $title) {
            $this->validateString($title, $constraint);
        }
    }

    /**
     * @param string     $title
     * @param Constraint $constraint
     */
    public function validateString($title, Constraint $constraint)
    {
        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->atPath($constraint->key . 'title')
            ->validate($title, new Length(['min' => 0, 'max' => 100]));
    }
}
