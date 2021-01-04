<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\ParticipantContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\ParticipantManager;

class ParticipantContextProxy implements ParticipantContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var ParticipantManager */
    private $participantManager;

    /**
     * @param StorageInterface   $storage
     * @param ParticipantManager $participantManager
     */
    public function __construct(StorageInterface $storage, ParticipantManager $participantManager)
    {
        $this->storage            = $storage;
        $this->participantManager = $participantManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return ParticipantManager
     */
    public function getParticipantManager()
    {
        return $this->participantManager;
    }
}
