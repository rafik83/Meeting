<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class UploadedObjectView
{
    /** @var string */
    public $path;

    /** @var string */
    public $filename;

    /** @var bool */
    public $crypted;

    public function __construct(string $path, string $filename, bool $crypted)
    {
        $this->path = $path;
        $this->filename = $filename;
        $this->crypted = $crypted;
    }
}
