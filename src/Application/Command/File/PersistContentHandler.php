<?php

namespace Proximum\Vimeet\Application\Command\File;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\File;

class PersistContentHandler
{
    /** @var FileFactory */
    private $fileFactory;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $pathToExport;

    public function __construct(
        FileFactory $fileFactory,
        FileStorageInterface $fileStorage,
        string $pathToExport,
        \DateTimeInterface $dateTime
    ) {
        $this->fileFactory = $fileFactory;
        $this->fileStorage = $fileStorage;
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

        return $this->fileFactory->createAndPersistFile($path);
    }
}
