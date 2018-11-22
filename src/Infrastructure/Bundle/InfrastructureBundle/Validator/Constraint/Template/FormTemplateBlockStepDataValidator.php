<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Domain\Template\Block;
use Symfony\Component\Validator\Constraint;

class FormTemplateBlockStepDataValidator extends ParticipantDataValidator
{
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
            $class      = $this->objectsConstraint[$object->getType()];
            $constraint = new $class(['key' => $key]);
            $validator->validate($object, $constraint, ['form_template_block_step', 'Default']);
        }
    }
}
