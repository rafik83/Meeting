<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\MeetingManager;
use Proximum\Vimeet\Behat\Service\Manager\SlotManager;
use Proximum\Vimeet\Behat\Service\Manager\SpotManager;

interface MeetingContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @return MeetingManager
     */
    public function getMeetingManager();

    /**
     * @return SpotManager
     */
    public function getSpotManager(): SpotManager;

    /**
     * @return SlotManager
     */
    public function getSlotManager(): SlotManager;
}
