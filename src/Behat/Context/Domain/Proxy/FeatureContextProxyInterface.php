<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;

interface FeatureContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();
}
