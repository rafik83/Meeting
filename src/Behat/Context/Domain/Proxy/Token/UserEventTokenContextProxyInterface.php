<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Token;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Token\UserEventTokenManager;

interface UserEventTokenContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @return UserEventTokenManager
     */
    public function getUserEventTokenManager();
}
