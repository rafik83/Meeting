<?php

namespace Proximum\Vimeet\Tests\Application\Query\Transaction;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Transaction\TransactionViewQuery;
use Proximum\Vimeet\Application\Query\Transaction\TransactionViewQueryHandler;
use Proximum\Vimeet\Application\View\Transaction\TransactionView;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Payment\PaymentRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class TransactionViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime       = new \DateTime();
        $event          = EventFactory::createEvent();
        $sheet          = SheetFactory::create($event);
        $payment        = new Payment();
        $transaction    = new Transaction($sheet, 100, $dateTime, 'paypal', '42', 'paid', 'EUR', $sheet->getOwner());
        $address        = new Address('42 rue des bonnes pratiques', '42', 'Paris', 'France');
        $billingInfos   = new BillingInfo($sheet);

        $billingInfos->update(
            'mr',
            'atwood',
            'jeff',
            'dev',
            '0000000000',
            '0000000000',
            'jeff@elao.fr',
            'codingHorror',
            $address,
            'FR42',
            'FR42'
        );

        $sheetInfoGuesser       = $this->prophesize(SheetInfoGuesserCache::class);
        $billingInfosRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $paymentRepository      = $this->prophesize(PaymentRepositoryInterface::class);
        $transactionViewQuery   = new TransactionViewQuery(
            $transaction,
            $sheet,
            $event,
            $payment
        );

        $sheetInfoGuesser->guessSheetTitle($sheet, $sheet->getEvent()->getFallback())->shouldBeCalled();
        $billingInfosRepository->getBySheet($sheet)->shouldBeCalled();

        $queryHandler = new TransactionViewQueryHandler(
            $sheetInfoGuesser->reveal(),
            $billingInfosRepository->reveal(),
            $paymentRepository->reveal()
        );

        $result = $queryHandler->handle($transactionViewQuery);

        $expected = new TransactionView(
            $event,
            $sheet->getId(),
            $event->getId(),
            $event->getTitle(),
            $sheet->getOwner()->getId(),
            null,
            $transaction->getDate(),
            $transaction->getMode(),
            $transaction->getReference(),
            null,
            $transaction->getAmount(),
            null,
            null
        );

        $this->assertEquals($expected, $result);
    }
}
