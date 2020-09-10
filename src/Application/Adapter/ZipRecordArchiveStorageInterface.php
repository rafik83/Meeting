<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Event;

interface ZipRecordArchiveStorageInterface
{
    public function prepareZip(array $files, string $zipName): void;
    public function upload(
        string $localPath,
        Event $event,
        string $fileName
    ): string;
    public function download(string $remotePath, string $localPath): bool;
    public function delete(string $remotePath): void;
}
