<?php

namespace Proximum\Vimeet\Behat\Proxy\Unavailability;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Unavailability\MassContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Unavailability\MassManager;

class MassContextProxy implements MassContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var MassManager */
    private $massManager;

    public function __construct(
        StorageInterface $storage,
        MassManager $massManager
    ) {
        $this->storage = $storage;
        $this->massManager = $massManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getMassManager(): MassManager
    {
        return $this->massManager;
    }
}
