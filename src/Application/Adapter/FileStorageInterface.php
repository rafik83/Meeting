<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @return null|string
     */
    public function upload($file, $directoryPath = null);

    /**
     * Create a file with the content given
     *
     * @param mixed       $content
     * @param string      $filename      with possible extension
     * @param string|null $directoryPath
     *
     * @return string return the filePath with filename after the directory path
     */
    public function create($content, $filename, $directoryPath = null);

    /**
     * @param string $identifier
     * @param bool   $fullPath
     *
     * @return FileStorageInterface
     */
    public function remove($identifier, $fullPath = false);

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
