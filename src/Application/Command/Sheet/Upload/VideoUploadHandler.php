<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Upload;

use Proximum\Vimeet\Application\Adapter\VideoStorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoUploadHandler
{
    /** @var VideoStorageInterface */
    private $videoStorage;

    public function __construct(
        VideoStorageInterface $videoStorage
    ) {
        $this->videoStorage = $videoStorage;
    }

    public function handle(VideoUpload $videoUpload): array
    {
        $object = $videoUpload->videoObject;
        $file = $object->getFile();

        if (!$file instanceof UploadedFile) {
            return $object->getDefaultValue();
        }

        if ($object->getPath()) {
            $this->videoStorage->remove($object->getPath());
        }

        $path = $this->videoStorage->upload($file);

        return [
            'path' => $path,
            'mime-type' => $file->getMimeType(),
        ];
    }
}
