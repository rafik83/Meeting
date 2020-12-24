<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\EventContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Event\AccessManager;
use Proximum\Vimeet\Behat\Service\Manager\EventManager;

class EventContextProxy implements EventContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var EventManager */
    private $eventManager;

    /** @var AccessManager */
    private $accessManager;

    /**
     * @param StorageInterface $storage
     * @param EventManager     $eventManager
     * @param AccessManager    $accessManager
     */
    public function __construct(
        StorageInterface $storage,
        EventManager $eventManager,
        AccessManager $accessManager
    ) {
        $this->storage = $storage;
        $this->eventManager = $eventManager;
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return AccessManager
     */
    public function getAccessManager()
    {
        return $this->accessManager;
    }
}
