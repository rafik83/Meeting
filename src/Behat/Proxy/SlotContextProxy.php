<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\SlotContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\SlotManager;

class SlotContextProxy implements SlotContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var SlotManager */
    private $slotManager;

    /**
     * @param StorageInterface $storage
     * @param SlotManager      $slotManager
     */
    public function __construct(StorageInterface $storage, SlotManager $slotManager)
    {
        $this->storage      = $storage;
        $this->slotManager = $slotManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return SlotManager
     */
    public function getSlotManager()
    {
        return $this->slotManager;
    }
}
