<?php

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
