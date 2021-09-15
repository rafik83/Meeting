<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\GroupContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\GroupManager;

class GroupContextProxy implements GroupContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var GroupManager */
    private $groupManager;

    /**
     * @param StorageInterface $storage
     * @param GroupManager     $groupManager
     */
    public function __construct(StorageInterface $storage, GroupManager $groupManager)
    {
        $this->storage      = $storage;
        $this->groupManager = $groupManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return GroupManager
     */
    public function getGroupManager()
    {
        return $this->groupManager;
    }
}
