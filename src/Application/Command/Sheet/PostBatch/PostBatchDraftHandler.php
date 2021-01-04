<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetDraftEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchDraftHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var SheetIndexerInterface
     */
    private $sheetIndexer;

    /**
     * PostBatchValidationDraftHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $datetime
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $datetime,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
        $this->sheetIndexer    = $sheetIndexer;
    }

    /**
     * @param PostBatchDraft $command
     */
    public function handle(PostBatchDraft $command)
    {
        foreach ($command->sheets as $sheet) {
            if (!$sheet->isValidationDraft()) {
                $this->eventDispatcher->dispatch(
                    Events::SHEET_VALIDATION_DRAFT,
                    new SheetDraftEvent(
                        $sheet,
                        $command->admin,
                        $this->datetime
                    )
                );
            }
        }

        $this->sheetIndexer->updateSheets($command->sheets);
    }
}
