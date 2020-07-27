<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\MimeType\MimeType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Video extends EditableObject implements UploadableObjectInterface
{
    public const ALLOWED_FORMATS = [
        MimeType::FORMAT_VIDEO,
    ];

    /** @var null|UploadedFile */
    public $file;

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

    public static function supportedMimeType(): array
    {
        return MimeType::getMimeTypesByFormats([MimeType::FORMAT_VIDEO]);
    }

    public function isVideoAndHasPath(): bool
    {
        return $this->isVideo() && $this->getPath();
    }
}
