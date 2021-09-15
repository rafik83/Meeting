<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNull;

class BooleanObjectValidator extends TemplateObjectValidator
{
    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\BooleanObject && true === $object->getRequired()) {
            $this->context->getValidator()
                ->inContext($this->context)
                ->atPath($constraint->key . '.boolean')
                ->validate($object->getContentValue(), new NotNull());
        }
    }
}
