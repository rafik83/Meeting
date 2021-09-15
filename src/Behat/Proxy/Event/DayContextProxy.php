<?php

namespace Proximum\Vimeet\Behat\Proxy\Event;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Event\DayContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Event\DayManager;

class DayContextProxy implements DayContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var DayManager */
    private $dayManager;

    /**
     * @param StorageInterface $storage
     * @param DayManager       $dayManager
     */
    public function __construct(StorageInterface $storage, DayManager $dayManager)
    {
        $this->storage    = $storage;
        $this->dayManager = $dayManager;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return DayManager
     */
    public function getDayManager()
    {
        return $this->dayManager;
    }
}
