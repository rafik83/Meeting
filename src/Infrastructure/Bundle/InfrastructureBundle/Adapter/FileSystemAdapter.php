<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileSystemAdapter implements FileSystemAdapterInterface
{
    /**
     * @var Filesystem
     */
    public $fileSystem;

    /**
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * {@inheritdoc}
     */
    public function dumpFile($filename, $content)
    {
        $this->fileSystem->dumpFile($filename, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($files): bool
    {
        return $this->fileSystem->exists($files);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($files): void
    {
        $this->fileSystem->remove($files);
    }
}
