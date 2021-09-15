<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\OrderContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\OrderManager;

class OrderContextProxy implements OrderContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var OrderManager */
    private $orderManager;

    /**
     * @param StorageInterface $storage
     * @param OrderManager     $orderManager
     */
    public function __construct(StorageInterface $storage, OrderManager $orderManager)
    {
        $this->storage      = $storage;
        $this->orderManager = $orderManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderManager()
    {
        return $this->orderManager;
    }
}
