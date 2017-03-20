<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\MeetingContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\MeetingManager;

class MeetingContextProxy implements MeetingContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var MeetingManager */
    private $meetingManager;

    /**
     * @param StorageInterface $storage
     * @param MeetingManager   $meetingManager
     */
    public function __construct(StorageInterface $storage, MeetingManager $meetingManager)
    {
        $this->storage        = $storage;
        $this->meetingManager = $meetingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return MeetingManager
     */
    public function getMeetingManager()
    {
        return $this->meetingManager;
    }
}
