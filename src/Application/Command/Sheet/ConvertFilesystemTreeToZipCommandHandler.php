<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Symfony\Component\Finder\Finder;

class ConvertFilesystemTreeToZipCommandHandler
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileSystemAdapterInterface $fileSystemAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->fileRepository = $fileRepository;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->dateTime = $dateTime;
    }

    public function handle(ConvertFilesystemTreeToZipCommand $command): void
    {
        $path = explode('/', $command->rootDir);
        $rootFolder = end($path);

        $finder = new Finder();
        $files = $finder->files()->in($command->rootDir);

        if (0 === $files->count()) {
            return;
        }

        $zip = new \ZipArchive();

        if (true !== $zip->open($command->rootDir . '.zip', \ZipArchive::CREATE)) {
            return;
        }

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $zip->addFile(
                $file->getRealPath(),
                str_replace($command->rootDir . '/', '', $file->getRealPath())
            );
        }

        if ($command->password) {
            $zip->setPassword($command->password);
        }

        $zip->close();

        $this->fileRepository->add(new File($rootFolder . '.zip', $this->dateTime));
        $this->fileSystemAdapter->remove($command->rootDir);
    }
}
