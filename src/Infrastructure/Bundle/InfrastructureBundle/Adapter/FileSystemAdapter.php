<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\UuidGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileSystemAdapter implements FileSystemAdapterInterface
{
    /** @var Filesystem */
    public $fileSystem;

    /** @var UuidGeneratorInterface */
    public $uuidGenerator;

    /**
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem, UuidGeneratorInterface $uuidGenerator)
    {
        $this->fileSystem = $fileSystem;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function dumpFile(string $filename, string $content)
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

    /**
     * {@inheritdoc}
     */
    public function rename(string $origin, string $target, bool $overwrite = false): void
    {
        $this->fileSystem->rename($origin, $target, $overwrite);
    }

    /**
     * {@inheritdoc}
     */
    public function mkdir($dirs, int $mode = 0777): void
    {
        $this->fileSystem->mkdir($dirs);
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = false): void
    {
        $this->fileSystem->copy($originFile, $targetFile, $overwriteNewerFiles);
    }

    /**
     * Create temporary directory and return path
     */
    public function createTempDir(): string
    {
        $path = $this->generateTemporaryPath();
        $this->mkdir($path);

        return $path;
    }

    /**
     * Generate path to temporary file or directory (file is not created)
     */
    public function generateTemporaryPath(): string
    {
        $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'vimeet';
        // create just container dir, to avoid error if path is used to copy file
        $this->mkdir($tempDir, 0600);

        return $tempDir.DIRECTORY_SEPARATOR.$this->uuidGenerator->generate();;
    }
}
