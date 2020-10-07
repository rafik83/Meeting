<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Behat\Transliterator\Transliterator;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LocalFileStorageAdapter implements FileStorageInterface
{
    /**
     * @var string
     */
    private $publicDir;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var FileSystemAdapterInterface
     */
    private $fileSystemAdapter;

    /**
     * LocalFileStorageAdapter constructor.
     *
     * @param FileSystemAdapterInterface $fileSystemAdapter
     * @param string                     $publicDir
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        FileSystemAdapterInterface $fileSystemAdapter,
        $publicDir,
        \DateTimeInterface $dateTime
    ) {
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->publicDir         = $publicDir;
        $this->dateTime          = $dateTime;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function upload($file, $directoryPath = null)
    {
        if (null === $file) {
            return null;
        }

        if (!$file instanceof UploadedFile) {
            throw new \Exception(sprintf('"%s" expected, "%s" given.', UploadedFile::class, is_object($file) ? get_class($file) : gettype($file)));
        }

        $path = $this->getAnnualizedPath('uploads/');

        $directory = (null === $directoryPath) ? $this->publicDir . $path : $directoryPath . $path;

        $extension = '.' . $file->guessExtension();
        $prefix    = uniqid() . '_';
        $filename  = $prefix . Transliterator::urlize(basename($file->getClientOriginalName(), $extension)) . $extension;

        $file->move($directory, $filename);

        return $path . '/' . $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function create($content, $filename, $directoryPath = null)
    {
        if (null === $directoryPath) {
            $directoryPath = $this->publicDir;
        }

        $filePath = sprintf('%s/%s_%s', $this->getAnnualizedPath(), uniqid(), $filename);

        $this->fileSystemAdapter->dumpFile(sprintf('%s/%s', $directoryPath, $filePath), $content);

        return $filePath;
    }

    /**
     * @param string|null $extraDirInPath should be a string ending with a "/"
     *
     * @return string
     */
    public function getAnnualizedPath($extraDirInPath = null)
    {
        return sprintf('/%s%s/%s', $extraDirInPath, $this->dateTime->format('Y'), $this->dateTime->format('m'));
    }

    /**
     * @param UploadedFile $file
     *
     * @return null|string
     */
    public function getExtension(UploadedFile $file)
    {
        return $file->guessExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier, $fullPath = false)
    {
        if (!empty($identifier)) {
            $filepath = (false === $fullPath) ? $this->publicDir . $identifier : $identifier;

            if (file_exists($filepath) && is_file($filepath) && is_writable($filepath)) {
                unlink($filepath);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function copyAndRename($identifier, $name = null)
    {
        $filename = null;

        if (!empty($identifier)) {
            $filepath = $this->publicDir . $identifier;

            if (file_exists($filepath) && is_file($filepath) && is_writable($filepath)) {
                $pathInfo = pathinfo($identifier);

                if (null !== $name) {
                    $filename = $name;

                    copy($filepath, $this->publicDir . $filename);
                } else {
                    $filename = sprintf(
                        '%s/%s_%s.%s',
                        $pathInfo['dirname'],
                        $pathInfo['filename'],
                        uniqid(),
                        $pathInfo['extension']
                    );

                    $file = sprintf(
                        '%s%s',
                        $this->publicDir,
                        $filename
                    );

                    copy($filepath, $file);
                }
            }
        }

        return $filename;
    }

    public function rename(string $origin, string $target, bool $overwrite = false): void
    {
        $this->fileSystemAdapter->rename($origin, $target, $overwrite);
    }

    public function getContents(string $filename): string
    {
        return file_get_contents($filename);
    }
}
