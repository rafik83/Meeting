<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;

class ImportHandler
{
    /**
     * @var FileStorageInterface
     */
    private $localFileStorageAdapter;

    /**
     * ImportHandler constructor.
     *
     * @param FileStorageInterface $localFileStorageAdapter
     */
    public function __construct(FileStorageInterface $localFileStorageAdapter)
    {
        $this->localFileStorageAdapter = $localFileStorageAdapter;
    }

    /**
     * @param Import $command
     */
    public function handle(Import $command)
    {
        $filePath = $this->localFileStorageAdapter->upload($command->file); //TODO: catch exception
    }
}
