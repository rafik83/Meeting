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

interface UploadableObjectInterface extends ContentObjectInterface
{
    public function getFile(): ?UploadedFile;
    public function setFile(?UploadedFile $file): void;
}
