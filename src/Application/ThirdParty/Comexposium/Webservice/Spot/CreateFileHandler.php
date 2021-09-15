<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class CreateFileHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $exportPath;

    public function __construct(
        FileStorageInterface $fileStorage,
        FileRepositoryInterface $fileRepository,
        string $exportPath,
        \DateTimeInterface $dateTime
    ) {
        $this->fileStorage = $fileStorage;
        $this->fileRepository = $fileRepository;
        $this->exportPath = $exportPath;
        $this->dateTime = $dateTime;
    }

    public function handle(CreateFile $createFile): File
    {
        $fileName = sprintf(
            'export_spots_%s_%s.csv',
            $createFile->event->getId(),
            $this->dateTime->format('Y-m-d_H-i-s')
        );
        $path = $this->fileStorage->create($createFile->content, $fileName, $this->exportPath);

        $file = new File($path, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
