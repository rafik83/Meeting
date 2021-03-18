<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\RemoveDecryptedFileEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoveDecryptedFileSubscriber implements EventSubscriberInterface
{
    private $fileSystemAdapter;

    public function __construct(FileSystemAdapterInterface $fileSystemAdapter)
    {
        $this->fileSystemAdapter = $fileSystemAdapter;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REMOVE_DECRYPTED_FILE => 'removeDecryptedFile',
        ];
    }

    public function removeDecryptedFile(RemoveDecryptedFileEvent $event): void
    {
        $this->fileSystemAdapter->remove($event->path);
    }
}
