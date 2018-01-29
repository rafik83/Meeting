<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\PackageContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\PackageManager;

class PackageContextProxy implements PackageContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var PackageManager */
    private $packageManager;

    /**
     * @param StorageInterface $storage
     * @param PackageManager   $packageManager
     */
    public function __construct(StorageInterface $storage, PackageManager $packageManager)
    {
        $this->storage = $storage;
        $this->packageManager = $packageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageManager(): PackageManager
    {
        return $this->packageManager;
    }
}
