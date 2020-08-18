<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\UuidGeneratorInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Domain\Model\Event;

class ZipRecordArchiveStorageAdapter implements ZipRecordArchiveStorageInterface
{
    /** @var \ZipArchive */
    public $zipArchive;

    /** @var GoogleCloudStorageAdapter */
    private $googleCloudStorage;

    /** @var string */
    private $googleCloudStorageUri;

    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    public function __construct(
        GoogleCloudStorageAdapter $googleCloudStorage,
        string $googleCloudStorageUri,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->googleCloudStorage = $googleCloudStorage;
        $this->googleCloudStorageUri = $googleCloudStorageUri;
        $this->zipArchive = new \ZipArchive();
        $this->uuidGenerator = $uuidGenerator;
    }

    public function prepareZip(array $files, string $zipName): void
    {
        if (true !== $this->zipArchive->open($zipName, \ZipArchive::CREATE)) {
            return;
        }

        foreach ($files as $name => $url) {
            $this->zipArchive->addFromString(
                $name,
                file_get_contents($url)
            );
        }

        $this->zipArchive->close();
    }

    public function upload(
        string $localPath,
        Event $event,
        string $fileName
    ): string {
        $relativePath = sprintf(
            '/%s/%d/%s/%s',
            'webinar-archive',
            $event->getId(),
            $this->uuidGenerator->generate(),
            $fileName
        );

        $this->googleCloudStorage->upload($localPath, $relativePath);

        return sprintf('%s%s', $this->googleCloudStorageUri, $relativePath);
    }

    public function download(string $remotePath, string $localPath): bool
    {
        if (!$this->googleCloudStorage->has($remotePath)) {
            return false;
        }

        return $this->googleCloudStorage->download($remotePath, $localPath);
    }

    public function delete(string $remotePath): void
    {
        $relativePath = str_replace(
            $this->googleCloudStorageUri,
            '',
            $remotePath
        );

        if (!$this->googleCloudStorage->has($relativePath)) {
            return;
        }

        $this->googleCloudStorage->delete($relativePath);
    }
}
