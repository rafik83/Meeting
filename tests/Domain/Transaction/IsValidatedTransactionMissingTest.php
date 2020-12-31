<?php


namespace Proximum\Vimeet\Tests\Domain\Transaction;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Transaction\IsValidatedTransactionMissing;
use Proximum\Vimeet\Domain\View\OrderVatView;

class IsValidatedTransactionMissingTest extends TestCase
{
    public function testValidatedTransaction(): void
    {
        $balance = $this->prophesize(Balance::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderVatViewQueryHandler = $this->prophesize(OrderVatViewQueryHandler::class);
        $orderVatView = new OrderVatView(
            4,
            4,
            4,
            true,
            4,
            'mode',
            'currency',
            false,
            4,
            4,
            500,
            new VatListView(1,500,true,'mode'),
            new \DateTime(),
            null
        );

        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);
        $type->isPaymentRequired()->willReturn(true);

        $order = $this->prophesize(Order::class);

        $sheet->getType()->willReturn($type->reveal());

        $balance->getTotalPaid($sheet->reveal())->willReturn(300);

        $orderRepository->findNotCancelledBySheet($sheet->reveal())->willReturn([$order->reveal()]);

        $orderVatQuery = new OrderVatViewQuery($order->reveal());
        $orderVatViewQueryHandler->handle($orderVatQuery)->willReturn($orderVatView);

        $canDisplayNeedObjectiveFilter = new IsValidatedTransactionMissing(
            $orderRepository->reveal(),
            $orderVatViewQueryHandler->reveal(),
            $balance->reveal()
        );
        $result = $canDisplayNeedObjectiveFilter->isSatisfiedBy($sheet->reveal());

        $this->assertTrue($result);
    }

    public function testPaymentNotRequired(): void
    {
        $balance = $this->prophesize(Balance::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderVatViewQueryHandler = $this->prophesize(OrderVatViewQueryHandler::class);

        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);
        $type->isPaymentRequired()->willReturn(false);

        $sheet->getType()->willReturn($type->reveal());

        $canDisplayNeedObjectiveFilter = new IsValidatedTransactionMissing(
            $orderRepository->reveal(),
            $orderVatViewQueryHandler->reveal(),
            $balance->reveal()
        );
        $result = $canDisplayNeedObjectiveFilter->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }

    public function testNoOrders(): void
    {
        $balance = $this->prophesize(Balance::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderVatViewQueryHandler = $this->prophesize(OrderVatViewQueryHandler::class);

        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);
        $type->isPaymentRequired()->willReturn(true);

        $balance->getTotalPaid($sheet->reveal());

        $sheet->getType()->willReturn($type->reveal());

        $orderRepository->findNotCancelledBySheet($sheet->reveal())->willReturn([]);

        $canDisplayNeedObjectiveFilter = new IsValidatedTransactionMissing(
            $orderRepository->reveal(),
            $orderVatViewQueryHandler->reveal(),
            $balance->reveal()
        );
        $result = $canDisplayNeedObjectiveFilter->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }

    public function testOrderTotalLessThanBalanceTotal(): void
    {
        $balance = $this->prophesize(Balance::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderVatViewQueryHandler = $this->prophesize(OrderVatViewQueryHandler::class);
        $orderVatView = new OrderVatView(
            4,
            4,
            4,
            true,
            4,
            'mode',
            'currency',
            false,
            4,
            4,
            200,
            new VatListView(1,200,true,'mode'),
            new \DateTime(),
            null
        );

        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);
        $type->isPaymentRequired()->willReturn(true);

        $order = $this->prophesize(Order::class);

        $sheet->getType()->willReturn($type->reveal());

        $balance->getTotalPaid($sheet->reveal())->willReturn(300);

        $orderRepository->findNotCancelledBySheet($sheet->reveal())->willReturn([$order->reveal()]);

        $orderVatQuery = new OrderVatViewQuery($order->reveal());
        $orderVatViewQueryHandler->handle($orderVatQuery)->willReturn($orderVatView);

        $canDisplayNeedObjectiveFilter = new IsValidatedTransactionMissing(
            $orderRepository->reveal(),
            $orderVatViewQueryHandler->reveal(),
            $balance->reveal()
        );
        $result = $canDisplayNeedObjectiveFilter->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }
}
