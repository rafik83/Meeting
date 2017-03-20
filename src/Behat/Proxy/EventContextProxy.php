<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\EventContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class EventContextProxy implements EventContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param StorageInterface         $storage
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(StorageInterface $storage, EventRepositoryInterface $eventRepository)
    {
        $this->storage         = $storage;
        $this->eventRepository = $eventRepository;
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
    public function getEventRepository()
    {
        return $this->eventRepository;
    }
}
