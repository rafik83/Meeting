<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Application\Exception\Transaction\TransactionNotFoundException;
use Proximum\Vimeet\Application\Query\Transaction\TransactionListViewQuery;
use Proximum\Vimeet\Application\Query\Transaction\TransactionViewQuery;
use Proximum\Vimeet\Application\Query\Transaction\TransactionViewQueryHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Payment\PaymentRepositoryInterface;

class FilterHandler
{
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;
    
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;
    
    /**
     * @var TransactionViewQueryHandler
     */
    private $transactionViewQueryHandler;
    
    /**
     * FindHandler constructor.
     *
     * @param PaymentRepositoryInterface $paymentRepository
     * @param EventRepositoryInterface $eventRepository
     * @param TransactionViewQueryHandler $transactionViewQueryHandler
     */
    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        EventRepositoryInterface $eventRepository,
        TransactionViewQueryHandler $transactionViewQueryHandler
    ) {
        $this->paymentRepository           = $paymentRepository;
        $this->eventRepository             = $eventRepository;
        $this->transactionViewQueryHandler = $transactionViewQueryHandler;
    }
    
    /**
     * @param Filter $command
     *
     * @return TransactionListViewQuery
     *
     * @throws TransactionNotFoundException
     */
    public function handle(Filter $command)
    {
        $events = $this->eventRepository->getEventsByAdmin($command->admin);
        
        if (empty($events)) {
            throw new TransactionNotFoundException();
        }
        
        $payments = $this->paymentRepository->findPaidByDateRangeAndCrossEvent(
            $command->beginDate,
            $command->endDate,
            $events
        );
        
        $transactionViews = [];
        foreach ($payments as $payment ) {
            $transactionViews[] = $this->transactionViewQueryHandler->handle(new TransactionViewQuery(
                $payment->getTransaction(),
                $payment->getTransaction()->getSheet(),
                $payment,
                $payment->getTransaction()->getSheet()->getEvent()
            ));
        }
        
        return new TransactionListViewQuery($transactionViews);
    }
}
