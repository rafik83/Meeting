<?php

namespace Proximum\Vimeet\Application\Command\OMZ;

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
    private $pathToOmzExport;

    public function __construct(
        FileStorageInterface $fileStorage,
        FileRepositoryInterface $fileRepository,
        string $pathToOmzExport,
        \DateTimeInterface $dateTime
    ) {
        $this->fileStorage = $fileStorage;
        $this->fileRepository = $fileRepository;
        $this->dateTime = $dateTime;
        $this->pathToOmzExport = $pathToOmzExport;
    }

    /**
     * @param PersistContent $command
     *
     * @return File
     */
    public function handle(PersistContent $command): File
    {
        $fileName = sprintf('export_participant_schedules_%s.csv', $this->dateTime->format('Y_m_d_His'));

        $path = $this->fileStorage->create(
            $command->content,
            $fileName,
            $this->pathToOmzExport
        );

        $file = new File($path, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
