<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\AvailabilityTimeRangeContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\AvailabilityTimeRangeManager;

class AvailabilityTimeRangeContextProxy implements AvailabilityTimeRangeContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var AvailabilityTimeRangeManager */
    private $availabilityTimeRangeManager;

    public function __construct(
        StorageInterface $storage,
        AvailabilityTimeRangeManager $availabilityTimeRangeManager
    ) {
        $this->storage = $storage;
        $this->availabilityTimeRangeManager = $availabilityTimeRangeManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getAvailabilityTimeRangeManager(): AvailabilityTimeRangeManager
    {
        return $this->availabilityTimeRangeManager;
    }
}
