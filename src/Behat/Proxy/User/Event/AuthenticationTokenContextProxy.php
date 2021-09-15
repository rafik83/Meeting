<?php

namespace Proximum\Vimeet\Behat\Proxy\User\Event;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\User\Event\AuthenticationTokenContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\User\Event\AuthenticationTokenManager;

class AuthenticationTokenContextProxy implements AuthenticationTokenContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var AuthenticationTokenManager */
    private $authenticationTokenManager;

    public function __construct(StorageInterface $storage, AuthenticationTokenManager $authenticationTokenManager)
    {
        $this->storage = $storage;
        $this->authenticationTokenManager = $authenticationTokenManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getAuthenticationTokenManager(): AuthenticationTokenManager
    {
        return $this->authenticationTokenManager;
    }
}
