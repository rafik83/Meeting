<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\ParticipantManager;

interface ParticipantContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * @return ParticipantManager
     */
    public function getParticipantManager();
}
