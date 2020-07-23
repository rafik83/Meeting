<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Messaging;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Messaging\MessageManager;

interface MessageContextProxyInterface
{
    public function getStorage(): StorageInterface;
    public function getMessageManager(): MessageManager;
}
