<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\CategoryContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\CategoryManager;

class CategoryContextProxy implements CategoryContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var CategoryManager */
    private $categoryManager;

    public function __construct(StorageInterface $storage, CategoryManager $categoryManager)
    {
        $this->storage = $storage;
        $this->categoryManager = $categoryManager;
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
    public function getCategoryManager(): CategoryManager
    {
        return $this->categoryManager;
    }
}
