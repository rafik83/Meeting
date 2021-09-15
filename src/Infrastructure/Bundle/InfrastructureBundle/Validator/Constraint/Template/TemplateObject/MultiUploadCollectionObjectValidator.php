<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\MimeType\MimeType;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class MultiUploadCollectionObjectValidator extends TemplateObjectValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof TemplateObject\MultiUploadCollectionObject) {
            $this->checkRequired($value, $constraint);
            $this->checkMaxUpload($value, $constraint);
            $this->checkMultiUploadObject($value, $constraint);
        }
    }

    protected function checkRequired(TemplateObject $object, Constraint $constraint): void
    {
        if ($object instanceof TemplateObject\MultiUploadCollectionObject
            && true === $object->getOption('required')
        ) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath($constraint->key)
                ->validate($object->getUploads(), new NotBlank());
        }
    }

    protected function checkMaxUpload(TemplateObject $object, Constraint $constraint): void
    {
        if ($object instanceof TemplateObject\MultiUploadCollectionObject && $object->getMax()) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath($constraint->key)
                ->validate(
                    $object->getUploads(),
                    new Count([
                        'max' => $object->getMax(),
                    ])
                );
        }
    }

    protected function checkMultiUploadObject(TemplateObject\MultiUploadCollectionObject $object, Constraint $constraint): void
    {
        $mimeTypes = MimeType::getMimeTypesByFormats($object->getFormats());
        if (!$mimeTypes) {
            return;
        }

        /** @var TemplateObject\MultiUploadObject $upload */
        foreach ($object->getUploads() as $index => $upload) {
            if (!$upload) {
                $this->context
                    ->buildViolation('No file was uploaded.')
                    ->atPath(sprintf('uploads.%s.file', $index))
                    ->addViolation();

                continue;
            }

            if (!$upload->getPath()) {
                $this->context
                    ->getValidator()
                    ->inContext($this->context)
                    ->atPath(sprintf('uploads.%s.file', $index))
                    ->validate($upload->getFile(), new NotBlank());
            }

            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('uploads.%s.file', $index))
                ->validate(
                    $upload->getFile(),
                    new File([
                        'mimeTypes' => $mimeTypes,
                        'mimeTypesMessage' => 'validators.multiUpload.file.notValid',
                    ])
                );

            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath(sprintf('uploads.%s.title', $index))
                ->validate($upload->getTitle(), new NotBlank());
        }
    }
}
