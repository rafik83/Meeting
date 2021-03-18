<?php

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

    /**
     * Renames a file or a directory.
     *
     * @param string $origin    The origin filename or directory
     * @param string $target    The new filename or directory
     * @param bool   $overwrite Whether to overwrite the target if it already exists
     */
    public function rename(string $origin, string $target, bool $overwrite = false): void;

    /**
     * Get file string content
     */
    public function getContents(string $filename): string;
}
