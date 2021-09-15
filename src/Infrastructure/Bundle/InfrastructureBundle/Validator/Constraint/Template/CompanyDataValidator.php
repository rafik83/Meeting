<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Domain\Template\TemplateData;
use Symfony\Component\Validator\Constraint;

class CompanyDataValidator extends ParticipantDataValidator
{
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
