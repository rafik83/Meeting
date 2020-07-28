<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\VideoStorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoStorageAdapter implements VideoStorageInterface
{
    public function upload($file): ?string
    {
        if (!$file instanceof UploadedFile) {
            return;
        }
    }

    public function remove($path): void
    {
        if (empty($path)) {
            return;
        }
    }
}
