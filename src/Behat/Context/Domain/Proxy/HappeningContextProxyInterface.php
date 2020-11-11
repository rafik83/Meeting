<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Happening\CategoryManager;
use Proximum\Vimeet\Behat\Service\Manager\HappeningManager;

interface HappeningContextProxyInterface
{
    public function getStorage(): StorageInterface;

    public function getHappeningManager(): HappeningManager;

    public function getCategoryManager(): CategoryManager;
}
