<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Unavailability;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Unavailability\MassManager;

interface MassContextProxyInterface
{
    public function getStorage(): StorageInterface;
    public function getMassManager(): MassManager;
}
