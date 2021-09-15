<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class GenderValidator extends TemplateObjectValidator
{
    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\Gender && true === $object->getOption('required')) {
            $this->context->getValidator()
                          ->inContext($this->context)
                          ->atPath($constraint->key . '.gender')
                          ->validate($object->getContentValue(), new NotBlank());
        }
    }
}
