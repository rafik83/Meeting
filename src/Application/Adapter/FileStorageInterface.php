<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileStorageInterface
{
    /**
     * Upload a file and return a string identifier
     *
     * @param mixed  $file
     * @param string $directoryPath
     *
     * @return string
     */
    public function upload($file, $directoryPath = null);

    /**
     * @param string $identifier
     *
     * @return FileStorageInterface
     */
    public function remove($identifier);

    /**
     * @param string      $identifier
     * @param string|null $name
     *
     * @return string|null
     */
    public function copyAndRename($identifier, $name = null);

    /**
     * @param UploadedFile $file
     *
     * @return string|null
     */
    public function getExtension(UploadedFile $file);
}
