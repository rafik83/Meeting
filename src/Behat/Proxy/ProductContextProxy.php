<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\ProductContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\ProductManager;

class ProductContextProxy implements ProductContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var ProductManager */
    private $productManager;

    /**
     * @param StorageInterface $storage
     * @param ProductManager   $productManager
     */
    public function __construct(StorageInterface $storage, ProductManager $productManager)
    {
        $this->storage = $storage;
        $this->productManager = $productManager;
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
    public function getProductManager(): ProductManager
    {
        return $this->productManager;
    }
}
