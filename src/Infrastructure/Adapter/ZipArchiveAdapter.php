<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\ZipArchiveAdapterInterface;
use Symfony\Component\Finder\SplFileInfo;

class ZipArchiveAdapter implements ZipArchiveAdapterInterface
{
    /** @var \ZipArchive */
    private $zipArchive;

    public function __construct()
    {
        $this->zipArchive = new \ZipArchive();
    }

    public function zipFiles(array $files, string $zipName, string $rootDir, ?string $password = null): void
    {
        if (true !== $this->zipArchive->open($zipName, \ZipArchive::CREATE)) {
            return;
        }

        if ($password) {
            $this->zipArchive->setPassword($password);
        }

        $directoryStructure = explode('/', $rootDir);
        $rootDirId = end($directoryStructure);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $relativePath = sprintf('%s/%s', $rootDirId, $file->getRelativePathname());

            $this->zipArchive->addFile(
                $file->getRealPath(),
                $relativePath
            );

            if ($password) {
                $this->zipArchive->setEncryptionName(
                    $relativePath,
                    \ZipArchive::EM_AES_256
                );
            }
        }
        $this->zipArchive->close();
    }
}
