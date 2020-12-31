<?php

namespace Proximum\Vimeet\Behat\Service\Adapter\SMS;

use Behat\Transliterator\Transliterator;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\SMSProviderInterface;

class StorageProvider implements SMSProviderInterface
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

    public function canSend(SMS $sms): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage(SMS $sms): void
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
