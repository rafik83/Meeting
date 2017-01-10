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
use Proximum\Vimeet\Infrastructure\Adapter\SessionAdapter;

class ImportHandler
{
    /**
     * @var FileStorageInterface
     */
    private $localFileStorageAdapter;

    /**
     * @var SessionAdapter
     */
    private $session;

    /**
     * @var string
     */
    private $publicDir;

    /**
     * ImportHandler constructor.
     *
     * @param FileStorageInterface $localFileStorageAdapter
     * @param SessionAdapter       $session
     * @param string               $publicDir
     */
    public function __construct(
        FileStorageInterface $localFileStorageAdapter,
        SessionAdapter $session,
        $publicDir
    ) {
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->session                 = $session;
        $this->publicDir               = $publicDir;
    }

    /**
     * @param Import $command
     */
    public function handle(Import $command)
    {
        $filePath = $this->localFileStorageAdapter->upload($command->file); //TODO: catch exception

        $this->session->set(Import::PARTICIPANT_IMPORT_FILE, $this->publicDir . $filePath);
        $this->session->set(Import::PARTICIPANT_IMPORT_CHARSET, $command->charset);
    }
}
