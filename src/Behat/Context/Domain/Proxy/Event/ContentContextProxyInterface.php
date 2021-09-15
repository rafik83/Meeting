<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Event;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Event\ContentManager;

interface ContentContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * @return ContentManager
     */
    public function getContentManager(): ContentManager;
}
