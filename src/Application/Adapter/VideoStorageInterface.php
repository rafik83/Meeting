<?php

namespace Proximum\Vimeet\Application\Adapter;

interface VideoStorageInterface
{
    /**
     * Upload a file and return a string identifier
     *
     * @param mixed  $file
     *
     * @return null|string
     */
    public function upload($file): ?string;

    public function remove($path): void;
}
