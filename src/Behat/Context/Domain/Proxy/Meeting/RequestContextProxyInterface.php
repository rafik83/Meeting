<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Meeting;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Meeting\RequestManager;

interface RequestContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * @return RequestManager
     */
    public function getRequestManager(): RequestManager;
}
