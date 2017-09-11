<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot\Import;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Serializer\Charset;

class SpotImportHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var string */
    private $importDir;

    /**
     * @param FileStorageInterface $fileStorage
     * @param string               $importDir
     */
    public function __construct(FileStorageInterface $fileStorage, string $importDir)
    {
        $this->fileStorage = $fileStorage;
        $this->importDir = $importDir;
    }

    public function handle(SpotImport $spotImport)
    {
        $filePath = $this->importDir . $this->fileStorage->upload($spotImport->file, $this->importDir);

        $filename = Charset::convert(
            $filePath,
            $spotImport->charset,
            Charset::UTF_8,
            $filePath
        );

        dump(file_get_contents($filename));
    }
}
