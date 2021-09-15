<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\HappeningContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Happening\CategoryManager;
use Proximum\Vimeet\Behat\Service\Manager\HappeningManager;

class HappeningContextProxy implements HappeningContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var HappeningManager */
    private $happeningManager;

    /** @var CategoryManager */
    private $categoryManager;

    public function __construct(
        StorageInterface $storage,
        HappeningManager $happeningManager,
        CategoryManager $categoryManager
    ) {
        $this->storage = $storage;
        $this->happeningManager = $happeningManager;
        $this->categoryManager = $categoryManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getHappeningManager(): HappeningManager
    {
        return $this->happeningManager;
    }

    public function getCategoryManager(): CategoryManager
    {
        return $this->categoryManager;
    }
}
