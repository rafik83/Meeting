<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadableObjectInterface extends ContentObjectInterface
{
    public function getFile(): ?UploadedFile;

    public function setFile(?UploadedFile $file): void;

    public function hasTag($tag);
}
