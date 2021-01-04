<?php

namespace Proximum\Vimeet\Application\Adapter;

interface ZipArchiveAdapterInterface
{
    public function zipFiles(array $files, string $zipName, string $rootDir, ?string $password = null): void;
}
