<?php

namespace Proximum\Vimeet\Application\Command\OMZ;

class ExportHandler
{
    /** @var PrepareContentHandler */
    private $prepareContentHandler;

    /** @var PersistContentHandler */
    private $persistContentHandler;

    /** @var NotifyHandler */
    private $notifyHandler;

    public function __construct(
        PrepareContentHandler $prepareContentHandler,
        PersistContentHandler $persistContentHandler,
        NotifyHandler $notifyHandler
    ) {
        $this->prepareContentHandler = $prepareContentHandler;
        $this->persistContentHandler = $persistContentHandler;
        $this->notifyHandler = $notifyHandler;
    }

    public function handle(Export $command): void
    {
        $content = $this->prepareContentHandler->handle(new PrepareContent($command->event));
        $file = $this->persistContentHandler->handle(new PersistContent($command->event, $content));
        $this->notifyHandler->handle(new Notify($command->event, $command->admin, $file));
    }
}
