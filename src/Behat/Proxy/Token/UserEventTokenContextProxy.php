<?php

namespace Proximum\Vimeet\Behat\Proxy\Token;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Token\UserEventTokenContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Token\UserEventTokenManager;

class UserEventTokenContextProxy implements UserEventTokenContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var UserEventTokenManager */
    private $userEventTokenManager;

    /**
     * @param StorageInterface      $storage
     * @param UserEventTokenManager $userEventTokenManager
     */
    public function __construct(StorageInterface $storage, UserEventTokenManager $userEventTokenManager)
    {
        $this->storage               = $storage;
        $this->userEventTokenManager = $userEventTokenManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEventTokenManager()
    {
        return $this->userEventTokenManager;
    }
}
