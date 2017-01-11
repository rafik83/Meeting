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
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Serializer\Charset;
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

        $filename = Charset::convert(
            $this->publicDir . $filePath,
            $command->charset,
            Charset::UTF_8,
            $this->publicDir . $filePath
        );

        $this->session->set(ParticipantImportTag::PARTICIPANT_IMPORT_FILE, $filename);
        $this->session->set(ParticipantImportTag::PARTICIPANT_IMPORT_CHARSET, $command->charset);
    }
}
