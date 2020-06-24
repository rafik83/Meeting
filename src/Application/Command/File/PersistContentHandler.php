<?php

namespace Proximum\Vimeet\Application\Command\File;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class PersistContentHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $pathToExport;

    public function __construct(
        FileStorageInterface $fileStorage,
        FileRepositoryInterface $fileRepository,
        string $pathToExport,
        \DateTimeInterface $dateTime
    ) {
        $this->fileStorage = $fileStorage;
        $this->fileRepository = $fileRepository;
        $this->dateTime = $dateTime;
        $this->pathToExport = $pathToExport;
    }

    public function handle(PersistContent $command): File
    {
        $fileName = sprintf($command->filenamePattern, $command->event->getId(), $this->dateTime->format('Y_m_d_His'));

        $path = $this->fileStorage->create(
            $command->content,
            $fileName,
            $this->pathToExport
        );

        $file = new File($path, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
