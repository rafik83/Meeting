<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Event\AccessManager;
use Proximum\Vimeet\Behat\Service\Manager\EventManager;

interface EventContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @return EventManager
     */
    public function getEventManager();

    /**
     * @return AccessManager
     */
    public function getAccessManager();
}
