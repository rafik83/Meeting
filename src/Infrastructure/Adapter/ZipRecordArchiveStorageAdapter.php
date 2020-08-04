<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Infrastructure\Adapter\GoogleCloudStorageAdapter;

class ZipRecordArchiveStorageAdapter implements ZipRecordArchiveStorageInterface
{
    /** @var GoogleCloudStorageAdapter */
    private $googleCloudStorage;

    public function __construct(
        GoogleCloudStorageAdapter $googleCloudStorage
    ) {
        $this->googleCloudStorage = $googleCloudStorage;
    }

    public function upload(string $localPath, string $remotePath): void
    {
        $this->googleCloudStorage->upload($localPath, $remotePath);
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
        if (!$this->googleCloudStorage->has($remotePath)) {
            return;
        }

        $this->googleCloudStorage->delete($remotePath);
    }
}
