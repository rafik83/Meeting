<?php

namespace Proximum\Vimeet\Behat\Proxy\Unavailability;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Unavailability\CategoryContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Unavailability\CategoryManager;

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
