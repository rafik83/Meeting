<?php

namespace Proximum\Vimeet\Behat\Proxy\Messaging;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Messaging\MessageContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Messaging\MessageManager;

class MessageContextProxy implements MessageContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var MessageManager */
    private $messageManager;

    public function __construct(
        StorageInterface $storage,
        MessageManager $messageManager
    ) {
        $this->storage = $storage;
        $this->messageManager = $messageManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getMessageManager(): MessageManager
    {
        return $this->messageManager;
    }
}
