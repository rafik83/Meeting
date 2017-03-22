<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Application\Query\Transaction\TransactionListViewQuery;
use Proximum\Vimeet\Application\Query\Transaction\TransactionViewQueryHandler;
use Proximum\Vimeet\Application\View\Transaction\TransactionView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Payment\PaymentRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class FilterHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime   = new \DateTime();
        $endDate    = new \DateTime('+ 1 day');
        $event      = EventFactory::createEvent();
        $admin      = new Admin('test@test.com', '__salt__', null, 'fr', 'Jeff', 'Atwood', Admin::ROLE_ORGANIZER, $dateTime);
        $type       = new Type($event);
        $payment    = new Payment();
        $command    = new Filter($admin);
    
        $command->beginDate = $dateTime;
        $command->endDate   = $endDate;
        
        $admin->setEvents([$event]);
        $admin->setTypeEvents([$type]);
    
        $eventRepository                = $this->prophesize(EventRepositoryInterface::class);
        $paymentRepository              = $this->prophesize(PaymentRepositoryInterface::class);
        $transactionViewQueryHandler    = $this->prophesize(TransactionViewQueryHandler::class);
        
        $eventRepository
            ->getEventsByAdmin($admin)
            ->shouldBeCalled()
            ->willReturn([$event]);
        
        $paymentRepository
            ->findPaidByDateRangeAndCrossEvent($command->beginDate, $command->endDate, [$event])
            ->shouldBeCalled()
            ->willReturn($payment);
        
        $filterHandler = new FilterHandler(
            $paymentRepository->reveal(),
            $eventRepository->reveal(),
            $transactionViewQueryHandler->reveal()
        );
        
        $filterHandler->handle($command);
    }
}
