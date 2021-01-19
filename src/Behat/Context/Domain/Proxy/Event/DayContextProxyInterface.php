<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Event;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Event\DayManager;

interface DayContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @return DayManager
     */
    public function getDayManager();
}
