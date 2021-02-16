<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Domain\Template\Block;
use Symfony\Component\Validator\Constraint;

class BlockValidator extends ParticipantDataValidator
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
            $validator->validate($object, $constraint, ['block', 'Default']);
        }
    }
}
