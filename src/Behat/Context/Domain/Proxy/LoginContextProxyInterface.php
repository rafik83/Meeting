<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;

interface LoginContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();
}
