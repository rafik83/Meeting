<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\SpotManager;

interface SpotContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @return SpotManager
     */
    public function getSpotManager();
}
