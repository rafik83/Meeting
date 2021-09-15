<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Event;

interface VideoStorageInterface
{
    /**
     * Upload a file and return a string identifier
     *
     * @param Event $event
     * @param mixed $file
     *
     * @return null|string
     */
    public function upload(Event $event, $file): ?string;

    public function remove($path): void;
}
