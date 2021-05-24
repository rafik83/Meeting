<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Payum;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Payum\CCIP\FindUnpaidOrders;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class FindUnpaidOrdersTest extends TestCase
{
    public function testFromOrderIds()
    {
        $sheet = SheetFactory::create();
        $user = UserFactory::create();
        $date = new \DateTime();

        $paidTransaction1 = Transaction::createForCcip($sheet, $user, 12, $date, [41,42]);
        $paidTransaction1->setPaid();
        $paidTransaction2 = Transaction::createForCcip($sheet, $user, 12, $date, [43]);
        $paidTransaction2->setPaid();
        $unpaidTransaction = Transaction::createForCcip($sheet, $user, 12, $date, [44]);

        $transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);
        $transactionRepository->findBySheet($sheet)
            ->shouldBeCalled()
            ->willReturn([$paidTransaction1, $paidTransaction2, $unpaidTransaction]);

        $findUnpaidOrders = new FindUnpaidOrders($transactionRepository->reveal());
        $result = $findUnpaidOrders->findBySheet($sheet);
        $this->assertEquals([44], $result);
    }
}
