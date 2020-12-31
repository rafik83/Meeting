<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Domain\Template\TemplateData;
use Symfony\Component\Validator\Constraint;

class ProfileDataValidator extends ParticipantDataValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof TemplateData) {
            $this->context->buildViolation('Template expected')->addViolation();
        }

        $validator = $this->context->getValidator()->inContext($this->context);

        foreach ($value->getProfileObjects() as $key => $object) {
            $class      = $this->objectsConstraint[$object->getType()];
            $constraint = new $class(['key' => $key]);
            $validator->validate($object, $constraint, ['profile', 'Default']);
        }
    }
}
