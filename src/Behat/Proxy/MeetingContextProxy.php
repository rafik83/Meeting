<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\MeetingContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\MeetingManager;
use Proximum\Vimeet\Behat\Service\Manager\SlotManager;
use Proximum\Vimeet\Behat\Service\Manager\SpotManager;

class MeetingContextProxy implements MeetingContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var MeetingManager */
    private $meetingManager;

    /** @var SpotManager */
    private $spotManager;

    /** @var SlotManager */
    private $slotManager;

    /**
     * @param StorageInterface $storage
     * @param MeetingManager   $meetingManager
     * @param SpotManager      $spotManager
     * @param SlotManager      $slotManager
     */
    public function __construct(
        StorageInterface $storage,
        MeetingManager $meetingManager,
        SpotManager $spotManager,
        SlotManager $slotManager
    ) {
        $this->storage        = $storage;
        $this->meetingManager = $meetingManager;
        $this->spotManager    = $spotManager;
        $this->slotManager    = $slotManager;
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
    public function getMeetingManager()
    {
        return $this->meetingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpotManager(): SpotManager
    {
        return $this->spotManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlotManager(): SlotManager
    {
        return $this->slotManager;
    }
}
