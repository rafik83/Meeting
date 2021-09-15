<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\MimeType\MimeType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadObject extends EditableObject implements UploadableObjectInterface
{
    public const ALLOWED_FORMATS = [
        MimeType::FORMAT_IMAGE,
        MimeType::FORMAT_VECTOR_IMAGE,
        MimeType::FORMAT_PDF,
        MimeType::FORMAT_PPT,
        MimeType::FORMAT_CSV,
    ];

    /** @var null|UploadedFile */
    private $file;

    /**
     * @return bool
     */
    public function isCrypted(): bool
    {
        return null !== $this->getOption('crypted') && true === $this->getOption('crypted');
    }

    /**
     * @return bool
     */
    public function isFilter(): bool
    {
        $filter = $this->getOption('filter');

        return null !== $filter
            && isset($filter['active'])
            && true === $filter['active'];
    }

    /**
     * @return string
     */
    public function getFilterLabel(): string
    {
        $filter = $this->getOption('filter');

        return null !== $filter
        && isset($filter['label'])
        && null !== $filter['label'] ? $filter['label'] : '';
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getPath(): ?string
    {
        return $this->data['path'] ?? null;
    }

    public function setPath(string $path): void
    {
        $this->data['path'] = $path;
    }

    public function getExtension(): ?string
    {
        return $this->data['extension'] ?? null;
    }

    public function setExtension(string $extension): void
    {
        $this->data['extension'] = $extension;
    }

    public function getContentValue(): string
    {
        return $this->getPath() ?: '';
    }

    public function getContentValueLocalize($locale = null): string
    {
        return $this->getContentValue();
    }

    public function getContentLabel(): string
    {
        return $this->getContentValue();
    }

    public function setContentValue($value): void
    {
        $this->setPath($value);
    }

    public function getFormats(): ?array
    {
        return $this->getOption('formats');
    }

    public function isImageFormat(): bool
    {
        return \in_array($this->getExtension(), MimeType::IMAGE_EXTENSIONS, true);
    }

    public function isUploadAndHasPath(): bool
    {
        return $this->isUpload() && $this->getPath();
    }
}
