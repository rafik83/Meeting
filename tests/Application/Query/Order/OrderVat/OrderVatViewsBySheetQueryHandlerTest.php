<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\OrderVat;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetQueryHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class OrderVatViewsBySheetQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet = SheetFactory::create();
        $order1 = new Order($sheet, [], new \DateTime('2017-01-01'));
        $order2 = new Order($sheet, [], new \DateTime('2017-02-23'));

        $orderRepositoryInterface = $this->prophesize(OrderRepositoryInterface::class);
        $orderVatViewQueryHandler = $this->prophesize(OrderVatViewQueryHandler::class);

        $orderVatViewsBySheetQueryHandler = new OrderVatViewsBySheetQueryHandler(
            $orderRepositoryInterface->reveal(),
            $orderVatViewQueryHandler->reveal()
        );

        $orderRepositoryInterface->findBySheet($sheet)->shouldBeCalled()->willReturn(
            [
                $order1,
                $order2,
            ]
        );

        $orderVatView1 = $this->prophesize(OrderVatView::class);
        $orderVatView2 = $this->prophesize(OrderVatView::class);

        $orderVatViewQueryHandler->handle(new OrderVatViewQuery($order1))->shouldBeCalled()->willReturn($orderVatView1->reveal());
        $orderVatViewQueryHandler->handle(new OrderVatViewQuery($order2))->shouldBeCalled()->willReturn($orderVatView2->reveal());

        $orderVatViews = $orderVatViewsBySheetQueryHandler->handle(new OrderVatViewsBySheetQuery($sheet));

        $this->assertEquals([$orderVatView1->reveal(), $orderVatView2->reveal()], $orderVatViews);
    }
}
