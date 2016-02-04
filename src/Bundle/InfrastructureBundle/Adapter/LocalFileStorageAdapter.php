<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Adapter;

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
        if (!$file instanceof UploadedFile) {
            throw new \Exception(sprintf('"%s" expected, "%s" given.', UploadedFile::class, is_object($file) ? get_class($file) : gettype($file)));
        }

        $path      = sprintf('/uploads/%s/%s', $this->dateTime->format('Y'), $this->dateTime->format('m'));
        $directory = $this->publicDir.$path;
        $extension = '.'.$file->getClientOriginalExtension();
        $prefix    = uniqid().'_';
        $filename  = $prefix.Transliterator::urlize(basename($file->getClientOriginalName(), $extension)).$extension;

        $file->move($directory, $filename);

        return $path.'/'.$filename;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        $filepath = $this->publicDir.$identifier;

        if (file_exists($filepath) && is_writable($filepath)) {
            unlink($filepath);
        }
    }
}
