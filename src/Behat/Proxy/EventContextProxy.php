<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\EventContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\EventManager;

class EventContextProxy implements EventContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var EventManager */
    private $eventManager;

    /**
     * @param StorageInterface $storage
     * @param EventManager     $eventManager
     */
    public function __construct(StorageInterface $storage, EventManager $eventManager)
    {
        $this->storage      = $storage;
        $this->eventManager = $eventManager;
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
}
