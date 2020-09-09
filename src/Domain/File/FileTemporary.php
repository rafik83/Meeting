<?php

namespace Proximum\Vimeet\Domain\File;

class FileTemporary
{
    /** @var string can be a path to a local file or any path that can be copied with copy function (like http/https url) */
    private $tempFilePath;

    /** @var string */
    private $originalName;

    public function __construct(string $tempFilePath, string $originalName)
    {
        $this->tempFilePath = $tempFilePath;
        $this->originalName = $originalName;
    }

    public function getTempFilePath(): string
    {
        return $this->tempFilePath;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }
}
