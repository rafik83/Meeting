<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Adapter;

use Behat\Transliterator\Transliterator;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;

class StorageSMSSenderAdapter implements SMSSenderInterface
{
    /** @var FileSystemAdapterInterface */
    private $fileSystem;

    /** @var string */
    private $smsDirectory;

    public function __construct(FileSystemAdapterInterface $fileSystem, string $smsDirectory)
    {
        $this->fileSystem = $fileSystem;
        $this->smsDirectory = $smsDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function send(SMS $sms)
    {
        $filePath = $this->smsDirectory . DIRECTORY_SEPARATOR . self::getFileName($sms->getReceiver());

        // remove previous file if exists
        $this->fileSystem->remove($filePath);

        $this->fileSystem->dumpFile(
            $filePath,
            $sms->getMessage()
        );
    }

    public static function getFileName(string $receiver): string
    {
        return Transliterator::urlize($receiver);
    }
}
