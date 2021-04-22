<?php

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetInvoicedEvent;
use Proximum\Vimeet\Application\View\Sheet\SheetInvoicedView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BatchGenerateInvoiceHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var CreateHandler
     */
    private $createHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param CreateHandler            $createHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     * @param JobQueueInterface        $jobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        CreateHandler $createHandler,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime,
        JobQueueInterface $jobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->createHandler   = $createHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
        $this->jobQueue        = $jobQueue;
    }

    /**
     * @param BatchGenerateInvoice $batchGenerateInvoice
     */
    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchGenerateInvoice->sheetIds);

        if (0 === count($sheets)) {
            return;
        }

        // get event prefix
        $firstSheet = reset($sheets);

        if (false === $firstSheet) {
            return;
        }

        $event  = $firstSheet->getEvent();
        $prefix = $event->getInvoicePrefix();

        $sheetInvoicedViews = [];

        foreach ($sheets as $sheet) {
            if ($event === $sheet->getEvent()) {
                $invoices = $this->createHandler->handle(new Create($sheet, $prefix));

                if (!empty($invoices)) {
                    $sheetInvoicedViews[] = new SheetInvoicedView($sheet, $invoices);
                }
            }
        }

        if (!empty($sheetInvoicedViews)) {
            // send emailing
            $this->jobQueue->sendEmailing($batchGenerateInvoice->event, $batchGenerateInvoice->sheetIds, Events::SHEET_INVOICED);

            $this->eventDispatcher->dispatch(
                Events::SHEET_INVOICED,
                new SheetInvoicedEvent(
                    $batchGenerateInvoice->admin,
                    $event,
                    $this->dateTime,
                    $sheetInvoicedViews
                )
            );
        }
    }
}
