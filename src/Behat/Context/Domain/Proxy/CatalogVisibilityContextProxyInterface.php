<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\CatalogVisibilityManager;

interface CatalogVisibilityContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @return CatalogVisibilityManager
     */
    public function getCatalogVisibilityManager();
}
