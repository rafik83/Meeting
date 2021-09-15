<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetPendingEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchPendingHandler
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
     * @param SheetIndexerInterface    $sheetIndexer
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(
        SheetIndexerInterface $sheetIndexer,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetIndexer    = $sheetIndexer;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
    }

    /**
     * @param PostBatchPending $command
     */
    public function handle(PostBatchPending $command)
    {
        foreach ($command->sheets as $sheet) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_PENDING,
                new SheetPendingEvent(
                    $sheet,
                    $this->dateTime,
                    $command->admin
                )
            );
        }

        $this->sheetIndexer->updateSheets($command->sheets);
    }
}
