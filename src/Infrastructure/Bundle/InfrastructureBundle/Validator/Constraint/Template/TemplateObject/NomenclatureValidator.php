<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class NomenclatureValidator extends TemplateObjectValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof TemplateObject\Nomenclature) {
            $this->checkRequired($value, $constraint);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\Nomenclature && true === $object->getOption('required')) {
            if ($object->isSingles()) {
                if (null !== $object->getNomenclatureModel()) {
                    $depth = $object->getNomenclatureModel()->getDepth();
                    $reference = [
                        1 => 'first',
                        2 => 'second',
                        3 => 'third',
                    ];

                    $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.item.' . $reference[$depth])->validate($object->getItems(), new Constraints\NotBlank());
                }
            } elseif ($object->isRadios()) {
                $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.item')->validate($object->getItems(), new Constraints\NotBlank());
            } elseif ($object->isCheckboxes()) {
                $this->context->getValidator()->inContext($this->context)->atPath($constraint->key . '.items')->validate($object->getItems(), new Constraints\NotBlank());
            }
        }
    }
}
