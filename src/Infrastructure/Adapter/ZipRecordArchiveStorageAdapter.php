<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Infrastructure\Adapter\GoogleCloudStorageAdapter;

class ZipRecordArchiveStorageAdapter implements ZipRecordArchiveStorageInterface
{
    /** @var GoogleCloudStorageAdapter */
    private $googleCloudStorage;

    public function __construct(GoogleCloudStorageAdapter $googleCloudStorage)
    {
        $this->googleCloudStorage = $googleCloudStorage;
    }

    public function upload(string $localPath, string $remotePath): void
    {
        $this->googleCloudStorage->upload($localPath, $remotePath);
    }

    public function getUrl(string $path): string
    {
        // todo
        return '';
    }

}
