<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\SpotContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\SpotManager;

class SpotContextProxy implements SpotContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var SpotManager */
    private $spotManager;

    /**
     * @param StorageInterface $storage
     * @param SpotManager      $spotManager
     */
    public function __construct(StorageInterface $storage, SpotManager $spotManager)
    {
        $this->storage     = $storage;
        $this->spotManager = $spotManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return SpotManager
     */
    public function getSpotManager()
    {
        return $this->spotManager;
    }
}
