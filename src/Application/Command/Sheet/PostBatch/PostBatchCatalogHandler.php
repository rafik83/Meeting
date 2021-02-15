<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalogHandler;
use Proximum\Vimeet\Application\Components\Sheet\Request\EnableDisableManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetCatalogEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchCatalogHandler
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
     * @var EnableDisableManager
     */
    private $enableDisableManager;

    /**
     * @var SheetIndexerInterface
     */
    private $sheetIndexer;

    /**
     * PostBatchCatalogHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EnableDisableManager     $enableDisableManager
     * @param \DateTimeInterface       $dateTime
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EnableDisableManager $enableDisableManager,
        \DateTimeInterface $dateTime,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->eventDispatcher      = $eventDispatcher;
        $this->dateTime             = $dateTime;
        $this->enableDisableManager = $enableDisableManager;
        $this->sheetIndexer         = $sheetIndexer;
    }

    /**
     * @param PostBatchCatalog $command
     */
    public function handle(PostBatchCatalog $command)
    {
        $state = BatchCatalogHandler::ADD_CATALOG === $command->state;

        foreach ($command->sheets as $sheet) {
            // Disable/enable the requests
            $this->enableDisableManager->update(
                $sheet,
                $state
            );

            // trace state in catalog change only
            if ($sheet->isInCatalog() !== $state) {
                $this->eventDispatcher->dispatch(
                    Events::SHEET_CATALOG,
                    new SheetCatalogEvent(
                        $sheet,
                        $command->admin,
                        $this->dateTime,
                        $state
                    )
                );
            }
        }

        $this->sheetIndexer->updateSheets($command->sheets);
    }
}
