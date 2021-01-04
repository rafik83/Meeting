<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditableTextValidator extends TemplateObjectValidator
{
    /**
     * @param string                  $path
     * @param mixed                   $value
     * @param Constraint|Constraint[] $constraints
     * @param array|null              $groups
     */
    private function validateAt($path, $value, $constraints = null, $groups = null)
    {
        $this->context->getValidator()->inContext($this->context)->atPath($path)->validate($value, $constraints, $groups);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($constraint instanceof EditableTextConstraint && $object instanceof TemplateObject\EditableText && true === $object->isRequired()) {
            $this->validateAt($constraint->getPath(), $object->getContentValueLocalize(), new NotBlank());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkMinLength(TemplateObject $object, Constraint $constraint)
    {
        if ($constraint instanceof EditableTextConstraint
            && $object instanceof TemplateObject\EditableText && $object->hasMinLength()
        ) {
            $this->validateAt($constraint->getPath(), $object->getContentValueLocalize(), new Length([
                'min' => $object->getMinLength(),
            ]));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkMaxLength(TemplateObject $object, Constraint $constraint)
    {
        if ($constraint instanceof EditableTextConstraint
            && $object instanceof TemplateObject\EditableText && $object->hasMaxLength()
        ) {
            $this->validateAt($constraint->getPath(), $object->getContentValueLocalize(), new Length([
                'max' => $object->getMaxLength(),
            ]));
        }
    }
}
