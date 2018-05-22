<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Symfony\Component\EventDispatcher\Event;

class RemoveDecryptedFileEvent extends Event
{
    /** @var string */
    public $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
