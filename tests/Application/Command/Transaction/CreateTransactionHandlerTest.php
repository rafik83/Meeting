<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateTransactionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $user  = new User('test@test.fr', 'test', 'test', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());

        $transactionDate = new \DateTime();

        $create = new Create(
            $sheet,
            200,
            $transactionDate,
            Mode::PAYMENT_BANK_CARD,
            'transaction_O1',
            Transaction::STATE_PAID
        );

        $expectedTransaction = new Transaction(
            $sheet,
            200,
            $transactionDate,
            Mode::PAYMENT_BANK_CARD,
            'transaction_O1',
            Transaction::STATE_PAID,
            $sheet->getEvent()->getCurrency()
        );

        $transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);
        $transactionRepository->add($expectedTransaction)->shouldBeCalled();

        $handler = new CreateHandler($transactionRepository->reveal());
        $handler->handle($create);
    }
}
