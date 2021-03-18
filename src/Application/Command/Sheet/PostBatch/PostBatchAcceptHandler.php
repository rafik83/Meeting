<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchAcceptHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var SheetIndexerInterface
     */
    private $sheetIndexer;

    /**
     * PostBatchAcceptHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
        $this->sheetIndexer    = $sheetIndexer;
    }

    /**
     * @param PostBatchAccept $command
     */
    public function handle(PostBatchAccept $command)
    {
        foreach ($command->sheets as $sheet) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_ACCEPTED,
                new SheetAcceptedEvent(
                    $sheet,
                    $command->admin,
                    $this->dateTime
                )
            );
        }

        $this->sheetIndexer->updateSheets($command->sheets);
    }
}
