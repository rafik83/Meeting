<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Service\Storage;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

interface EventContextProxyInterface
{
    /**
     * @return Storage
     */
    public function getStorage();

    /**
     * @return EventRepositoryInterface
     */
    public function getEventRepository();
}
