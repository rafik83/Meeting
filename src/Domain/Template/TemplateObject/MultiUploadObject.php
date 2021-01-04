<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultiUploadObject
{
    /** @var null|string */
    private $uniqId;

    /** @var null|string */
    private $title;

    /** @var null|string */
    private $path;

    /** @var null|UploadedFile */
    private $file;

    public function __construct(?string $uniqId = null, ?string $title = null, ?string $path = null)
    {
        $this->uniqId = $uniqId;
        $this->title = $title;
        $this->path = $path;
    }

    public function getUniqId(): ?string
    {
        return $this->uniqId;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getDefaultValues(): array
    {
        return [
            'path' => $this->getPath(),
            'title' => $this->getTitle(),
            'uniqId' => $this->getUniqId(),
        ];
    }
}
