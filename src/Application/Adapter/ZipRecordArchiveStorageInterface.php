<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface ZipRecordArchiveStorageInterface
{
    public function upload(string $localPath, string $remotePath): void;
    public function getUrl(string $path): string;
}
