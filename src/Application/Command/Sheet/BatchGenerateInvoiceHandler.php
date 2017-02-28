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
use Proximum\Vimeet\Domain\Order\OrdersToInvoice;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;

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
     * BatchGenerateInvoiceHandler constructor.
     *
     * @param SheetRepositoryInterface   $sheetRepository
     * @param OrdersToInvoice            $ordersToInvoice
     * @param OrderRepositoryInterface   $orderRepository
     * @param CreateHandler              $createHandler
     */
    public function __construct(
        SheetRepositoryInterface   $sheetRepository,
        OrdersToInvoice            $ordersToInvoice,
        OrderRepositoryInterface   $orderRepository,
        CreateHandler              $createHandler
    ) {
        $this->sheetRepository   = $sheetRepository;
        $this->ordersToInvoice   = $ordersToInvoice;
        $this->orderRepository   = $orderRepository;
        $this->createHandler     = $createHandler;
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
        $invoiceGeneratedCounter = 0;
        
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
                
                $invoiceGeneratedCounter++;
            }
        }
        
        return $this->getBatchResult($batchGenerateInvoice, $invoiceGeneratedCounter);
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
