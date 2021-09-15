<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\ProductManager;

interface ProductContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * @return ProductManager
     */
    public function getProductManager(): ProductManager;
}
