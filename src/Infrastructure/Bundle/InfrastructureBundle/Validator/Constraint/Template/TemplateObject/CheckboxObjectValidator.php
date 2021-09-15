<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckboxObjectValidator extends TemplateObjectValidator
{
    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\CheckboxObject && true === $object->getRequired()) {
            $this->context->getValidator()
                ->inContext($this->context)
                ->atPath($constraint->key . '.checkbox')
                ->validate($object->getContentValue(), new NotBlank(['message' => 'validators.field.required_checkbox']));
        }
    }
}
