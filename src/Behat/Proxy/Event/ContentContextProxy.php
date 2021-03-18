<?php

namespace Proximum\Vimeet\Behat\Proxy\Event;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Event\ContentContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Event\ContentManager;

class ContentContextProxy implements ContentContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var ContentManager */
    private $contentManager;

    /**
     * @param StorageInterface $storage
     * @param ContentManager   $contentManager
     */
    public function __construct(StorageInterface $storage, ContentManager $contentManager)
    {
        $this->storage = $storage;
        $this->contentManager = $contentManager;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @return ContentManager
     */
    public function getContentManager(): ContentManager
    {
        return $this->contentManager;
    }
}
