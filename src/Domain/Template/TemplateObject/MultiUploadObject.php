<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultiUploadObject
{
    /** @var MultiUploadCollectionObject */
    private $collection;

    /** @var null|string */
    private $title;

    /** @var null|UploadedFile */
    private $file;

    public function __construct(MultiUploadCollectionObject $collection, ?string $title, ?UploadedFile $file)
    {
        $this->collection = $collection;
        $this->title = $title;
        $this->file = $file;
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

    public function getCollection(): MultiUploadCollectionObject
    {
        return $this->collection;
    }
}
