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
use Symfony\Component\Finder\Finder;

class ZipArchiveAdapter implements ZipArchiveAdapterInterface
{
    /** @var \ZipArchive */
    private $zipArchive;

    public function __construct()
    {
        $this->zipArchive = new \ZipArchive();
    }

    public function zipFiles(Finder $files, string $zipName, string $rootDir, ?string $password = null): void
    {
        if (true !== $this->zipArchive->open($zipName, \ZipArchive::CREATE)) {
            return;
        }

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $this->zipArchive->addFile(
                $file->getRealPath(),
                str_replace($rootDir . '/', '', $file->getRealPath())
            );
        }

        if ($password) {
            $this->zipArchive->setPassword($password);
        }

        $this->zipArchive->close();
    }
}
