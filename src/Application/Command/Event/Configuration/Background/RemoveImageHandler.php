<?php

namespace Proximum\Vimeet\Application\Command\Event\Configuration\Background;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;

class RemoveImageHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /**
     * @param FileStorageInterface $fileStorage
     */
    public function __construct(
        FileStorageInterface $fileStorage
    ) {
        $this->fileStorage = $fileStorage;
    }

    /**
     * @param RemoveImage $command
     */
    public function handle(RemoveImage $command): void
    {
        $this->fileStorage->remove($command->event->getConfiguration()->getBackgroundImage());
        $command->event->getConfiguration()->setBackgroundImage(null);
    }
}
