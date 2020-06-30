<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Happening;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Happening\CategoryManager;

interface CategoryContextProxyInterface
{
    /** @return StorageInterface */
    public function getStorage(): StorageInterface;

    /** @return CategoryManager */
    public function getCategoryManager(): CategoryManager;
}
