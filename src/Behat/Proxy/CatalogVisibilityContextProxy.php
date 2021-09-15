<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\CatalogVisibilityContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\CatalogVisibilityManager;

class CatalogVisibilityContextProxy implements CatalogVisibilityContextProxyInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var CatalogVisibilityManager
     */
    private $catalogVisibilityManager;

    /**
     * CatalogVisibilityContextProxy constructor.
     *
     * @param StorageInterface         $storage
     * @param CatalogVisibilityManager $catalogVisibilityManager
     */
    public function __construct(
        StorageInterface $storage,
        CatalogVisibilityManager $catalogVisibilityManager
    ) {
        $this->storage                  = $storage;
        $this->catalogVisibilityManager = $catalogVisibilityManager;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return CatalogVisibilityManager
     */
    public function getCatalogVisibilityManager()
    {
        return $this->catalogVisibilityManager;
    }
}
