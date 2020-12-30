<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class UrlValidator extends TemplateObjectValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);

        if ($value instanceof TemplateObject\Url) {
            if (null !== $value->getData()) {
                $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.url')->validate($value->getContentValue(), new Constraints\Url());
            }
        } else {
            $this->context->buildViolation('validators.field.notValid.url')->atPath($constraint->key . '.url')->addViolation();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\Url && true === $object->getOption('required')) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.url')->validate($object->getContentValue(), new Constraints\NotBlank());
        }
    }
}
