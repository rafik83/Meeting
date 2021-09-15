<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template;

use Proximum\Vimeet\Domain\Template;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;

class TemplateObjectValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof Template\TemplateObject) {
            $this->checkRequired($value, $constraint);
            $this->checkMinLength($value, $constraint);
            $this->checkMaxLength($value, $constraint);
        }
    }

    /**
     * @param Template\TemplateObject $object
     * @param Constraint              $constraint
     */
    protected function checkRequired(Template\TemplateObject $object, Constraint $constraint)
    {
        if (true === $object->getOption('required') && $object instanceof Template\TemplateObject\ContentObjectInterface) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key)->validate($object->getContentValue(), new NotBlank());
        }
    }

    /**
     * @param Template\TemplateObject $object
     * @param Constraint              $constraint
     */
    protected function checkMinLength(Template\TemplateObject $object, Constraint $constraint)
    {
        if (null !== $object->getOption('minLength') && $object instanceof Template\TemplateObject\ContentObjectInterface) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key)->validate($object->getContentValue(), new Length(['min' => $object->getOption('minLength')]));
        }
    }

    /**
     * @param Template\TemplateObject $object
     * @param Constraint              $constraint
     */
    protected function checkMaxLength(Template\TemplateObject $object, Constraint $constraint)
    {
        if (null !== $object->getOption('maxLength') && $object instanceof Template\TemplateObject\ContentObjectInterface) {
            $this->context->getValidator()->inContext($this->context)->atPath($constraint->key)->validate($object->getContentValue(), new Length(['max' => $object->getOption('maxLength')]));
        }
    }
}
