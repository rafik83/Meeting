<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot\Import;

use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

class SpotImportHandler
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var LocalFileStorageAdapter */
    private $fileStorage;

    /** @var string */
    private $importDir;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param LocalFileStorageAdapter $fileStorage
     * @param string                  $importDir
     * @param \DateTimeInterface      $dateTime
     */
    public function __construct(
        FileRepositoryInterface $fileRepository,
        LocalFileStorageAdapter $fileStorage,
        string $importDir,
        \DateTimeInterface $dateTime
    ) {
        $this->fileRepository = $fileRepository;
        $this->fileStorage = $fileStorage;
        $this->importDir = $importDir;
        $this->dateTime = $dateTime;
    }

    /**
     * @param SpotImport $spotImport
     *
     * @return File
     */
    public function handle(SpotImport $spotImport): File
    {
        $fileContent = file_get_contents($spotImport->file);

        $filePath = $this
            ->fileStorage
            ->create(
                $fileContent,
                basename($spotImport->file),
                $this->importDir
            );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
