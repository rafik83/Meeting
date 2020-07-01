<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Happening;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Happening\CategoryManager;

interface CategoryContextProxyInterface
{
    public function getStorage(): StorageInterface;
    public function getCategoryManager(): CategoryManager;
}
