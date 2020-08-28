<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\PackageManager;

interface PackageContextProxyInterface
{
    public function getStorage(): StorageInterface;
    public function getPackageManager(): PackageManager;
}
