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

class MultiUploadObject implements UploadableObjectInterface
{
    /** @var null|string */
    private $title;

    /** @var null|string */
    private $path;

    /** @var null|UploadedFile */
    private $file;

    public function __construct(?string $title, ?string $path)
    {
        $this->title = $title;
        $this->path = $path;
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

    /**
     * @return string
     */
    public function getContentValue()
    {
        // TODO: Implement getContentValue() method.
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getContentValueLocalize($locale = null)
    {
        // TODO: Implement getContentValueLocalize() method.
    }

    /**
     * @return string
     */
    public function getContentLabel()
    {
        // TODO: Implement getContentLabel() method.
    }

    /**
     * @param string|array $value
     */
    public function setContentValue($value)
    {
        // TODO: Implement setContentValue() method.
    }

    public function hasTag($tag)
    {
        // TODO: Implement hasTag() method.
    }
}
