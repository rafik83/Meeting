<?php

namespace Proximum\Vimeet\Behat\Proxy\Happening;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Happening\CategoryContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Happening\CategoryManager;

class CategoryContextProxy implements CategoryContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var CategoryManager */
    private $categoryManager;

    public function __construct(
        StorageInterface $storage,
        CategoryManager $categoryManager
    ) {
        $this->storage = $storage;
        $this->categoryManager = $categoryManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getCategoryManager(): CategoryManager
    {
        return $this->categoryManager;
    }
}
