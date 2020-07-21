<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Unavailability;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Unavailability\CategoryManager;

interface CategoryContextProxyInterface
{
    public function getStorage(): StorageInterface;

    public function getCategoryManager(): CategoryManager;
}
