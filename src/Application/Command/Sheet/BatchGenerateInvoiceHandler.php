<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Invoice\Create;
use Proximum\Vimeet\Application\Command\Invoice\CreateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetInvoicedEvent;
use Proximum\Vimeet\Domain\Order\OrdersToInvoice;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class BatchGenerateInvoiceHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var OrdersToInvoice
     */
    private $ordersToInvoice;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * @var CreateHandler
     */
    private $createHandler;
    
    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;
    
    /**
     * @var Sheet[]
     */
    private $sheetsInvoiced;
    
    /**
     * @var \DateTimeInterface
     */
    private $datetime;
    
    /**
     * BatchGenerateInvoiceHandler constructor.
     *
     * @param SheetRepositoryInterface  $sheetRepository
     * @param OrdersToInvoice           $ordersToInvoice
     * @param OrderRepositoryInterface  $orderRepository
     * @param CreateHandler             $createHandler
     * @param DelayedEventDispatcher    $eventDispatcher
     * @param \DateTimeInterface        $datetime
     */
    public function __construct(
        SheetRepositoryInterface   $sheetRepository,
        OrdersToInvoice            $ordersToInvoice,
        OrderRepositoryInterface   $orderRepository,
        CreateHandler              $createHandler,
        DelayedEventDispatcher     $eventDispatcher,
        \DateTimeInterface         $datetime
    ) {
        $this->sheetRepository   = $sheetRepository;
        $this->ordersToInvoice   = $ordersToInvoice;
        $this->orderRepository   = $orderRepository;
        $this->createHandler     = $createHandler;
        $this->eventDispatcher   = $eventDispatcher;
        $this->datetime          = $datetime;
    }
    
    /**
     * @param BatchGenerateInvoice $batchGenerateInvoice
     *
     * @return BatchResult
     */
    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchGenerateInvoice->ids);
        
        if (count($sheets) === 0) {
            return $this->getBatchResult($batchGenerateInvoice, 0);
        }
        
        $prefix = $sheets[0]->getEvent()->getInvoicePrefix();
        $event  = $sheets[0]->getEvent();
        
        foreach ($sheets as $sheet) {
            $ordersToInvoiceView = $this->ordersToInvoice->getOrdersToInvoiceViewForSheet($sheet);
            
            if ($ordersToInvoiceView instanceof OrdersToInvoiceView) {
                $create = new Create($sheet, $prefix, $ordersToInvoiceView);
                
                $invoice = $this->createHandler->handle($create);
                
                // Flag Order with generated Invoice
                foreach ($ordersToInvoiceView->getOrders() as $order) {
                    $order->setInvoice($invoice);
                    $this->orderRepository->set($order);
                }
                
                $this->sheetsInvoiced[] = $sheet;
            }
        }
    
        $this->eventDispatcher->dispatch(
            Events::SHEET_INVOICED,
            new SheetInvoicedEvent(
                $batchGenerateInvoice->admin,
                $event,
                $this->datetime,
                $this->sheetsInvoiced
            )
        );
        
        return $this->getBatchResult($batchGenerateInvoice, count($this->sheetsInvoiced));
    }
    
    /**
     * @param BatchGenerateInvoice $batchGenerateInvoice
     * @param $count
     *
     * @return BatchResult
     */
    private function getBatchResult(BatchGenerateInvoice $batchGenerateInvoice, $count)
    {
        return new BatchResult($count, $batchGenerateInvoice->getMessage() . 'generateInvoice.success');
    }
}
