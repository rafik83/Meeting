<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalog;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalogHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisableHandler;
use Proximum\Vimeet\Application\Components\Sheet\HappeningParticipation\EnableDisableManager as EnableDisableManagerHappening;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetEnableDisableEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchEnableDisableHandler
{
    /**
     * @var BatchCatalogHandler
     */
    private $batchCatalogHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var EnableDisableManagerHappening
     */
    private $enableDisableManagerHappening;

    /**
     * @var SheetIndexerInterface
     */
    private $sheetIndexer;

    /**
     * PostBatchEnableDisableHandler constructor.
     *
     * @param BatchCatalogHandler           $batchCatalogHandler
     * @param EventDispatcherInterface      $eventDispatcher
     * @param \DateTimeInterface            $dateTime
     * @param EnableDisableManagerHappening $enableDisableManagerHappening
     * @param SheetIndexerInterface         $sheetIndexer
     */
    public function __construct(
        BatchCatalogHandler $batchCatalogHandler,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime,
        EnableDisableManagerHappening $enableDisableManagerHappening,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->batchCatalogHandler           = $batchCatalogHandler;
        $this->eventDispatcher               = $eventDispatcher;
        $this->dateTime                      = $dateTime;
        $this->enableDisableManagerHappening = $enableDisableManagerHappening;
        $this->sheetIndexer                  = $sheetIndexer;
    }

    /**
     * @param PostBatchEnableDisable $command
     */
    public function handle(PostBatchEnableDisable $command)
    {
        $state = BatchEnableDisableHandler::STATE_ENABLE === $command->state;

        // remove sheet from catalog if sheet is disable
        if (BatchEnableDisableHandler::STATE_DISABLE === $command->state) {
            $this->batchCatalogHandler->handle(new BatchCatalog(
                $command->ids,
                false,
                $command->admin
            ));
        }

        foreach ($command->sheets as $sheet) {
            // Disable/enable the happenings
            $this->enableDisableManagerHappening->update(
                $sheet,
                $state
            );

            $this->eventDispatcher->dispatch(
                Events::SHEET_ENABLE_DISABLE,
                new SheetEnableDisableEvent(
                    $sheet,
                    $command->admin,
                    $this->dateTime,
                    $state
                )
            );
        }

        $this->sheetIndexer->updateSheets($command->sheets);
    }
}
