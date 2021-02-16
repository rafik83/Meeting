<?php

namespace Proximum\Vimeet\Domain\File;

use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class FileFactory
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(FileRepositoryInterface $fileRepository, \DateTimeInterface $dateTime)
    {
        $this->fileRepository = $fileRepository;
        $this->dateTime = $dateTime;
    }

    public function createAndPersistFile(
        string $path,
        string $type = File::TYPE_UNKNOWN
    ): File {
        $file = new File($path, $this->dateTime, $type);

        $this->fileRepository->add($file);

        return $file;
    }
}
