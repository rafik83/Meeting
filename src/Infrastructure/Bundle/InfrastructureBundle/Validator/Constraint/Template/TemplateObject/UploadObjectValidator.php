<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class UploadObjectValidator extends TemplateObjectValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof TemplateObject\UploadObject) {
            $this->checkRequired($value, $constraint);
        }
    }

    protected function checkRequired(TemplateObject $object, Constraint $constraint): void
    {
        if ($object instanceof TemplateObject\UploadObject &&
            true === $object->getOption('required') &&
            $object instanceof TemplateObject\ContentObjectInterface
        ) {
            $content = $object->getPath();

            if (null === $content) {
                $content = $object->getFile();
            }

            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('%s.file', $constraint->key))
                ->validate($content, new NotBlank());
        }
    }
}
