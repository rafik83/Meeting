<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\AdminContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\AdminManager;

class AdminContextProxy implements AdminContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var AdminManager */
    private $adminManager;

    /**
     * @param StorageInterface $storage
     * @param AdminManager     $adminManager
     */
    public function __construct(StorageInterface $storage, AdminManager $adminManager)
    {
        $this->storage = $storage;
        $this->adminManager = $adminManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminManager(): AdminManager
    {
        return $this->adminManager;
    }
}
