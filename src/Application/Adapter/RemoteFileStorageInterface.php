<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface RemoteFileStorageInterface
{
    /**
     * Upload a file to remote filesystem
     *
     * @param \SplFileInfo $file
     * @param string|null $remotePath
     */
    public function upload(\SplFileInfo $file, ?string $remotePath = null);

    /**
     * Download a file
     *
     * @param string $file
     * @param string $remotePath
     */
    public function download(string $remoteFilePath, string $localPath);
}
