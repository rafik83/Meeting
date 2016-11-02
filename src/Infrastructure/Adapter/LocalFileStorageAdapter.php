<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Behat\Transliterator\Transliterator;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
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
     * @var string
     */
    private $fileExtension;

    /**
     * LocalFileStorageAdapter constructor.
     *
     * @param string             $publicDir
     * @param \DateTimeInterface $dateTime
     */
    public function __construct($publicDir, \DateTimeInterface $dateTime)
    {
        $this->publicDir = $publicDir;
        $this->dateTime  = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function upload($file)
    {
        if (null === $file) {
            return;
        }

        if (!$file instanceof UploadedFile) {
            throw new \Exception(sprintf('"%s" expected, "%s" given.', UploadedFile::class, is_object($file) ? get_class($file) : gettype($file)));
        }

        $this->fileExtension = $file->guessExtension();

        $path      = sprintf('/uploads/%s/%s', $this->dateTime->format('Y'), $this->dateTime->format('m'));
        $directory = $this->publicDir . $path;
        $extension = '.' . $file->getClientOriginalExtension();
        $prefix    = uniqid() . '_';
        $filename  = $prefix . Transliterator::urlize(basename($file->getClientOriginalName(), $extension)) . $extension;

        $file->move($directory, $filename);

        return $path . '/' . $filename;
    }

    /**
     * @param UploadedFile $file
     *
     * @return null|string
     */
    public function getExtension(UploadedFile $file)
    {
        return $this->fileExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        if (!empty($identifier)) {
            $filepath = $this->publicDir . $identifier;

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
}
