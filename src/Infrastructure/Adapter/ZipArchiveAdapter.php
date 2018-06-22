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

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $filePath = str_replace($rootDir . '/', '', $file->getRealPath());

            $this->zipArchive->addFile(
                $file->getRealPath(),
                $filePath
            );

            if ($password) {
                $this->zipArchive->setEncryptionName($filePath, \ZipArchive::EM_AES_256);
            }
        }
        $this->zipArchive->close();
    }
}
