<?php

namespace Proximum\Vimeet\Tests\Application\Components\Order;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Components\Order\OrdersToInvoice;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQuery;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQueryHandler;
use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Application\View\Invoice\InvoiceDataView;
use Proximum\Vimeet\Application\View\Order\SummaryView;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class OrdersToInvoiceTest extends TestCase
{
    public function testGetInvoiceViewForSheet()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent('My event', 'fr');
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $plan = Product::createPlan($event, 'plan', '', 99, 20, 20, 100);
        $participant = Product::createParticipant($event, 'participant', 1789, 20, 20);
        $option = Product::createOption($event, 'option', '', 169, 20, 50, 10, 20, true);

        $orderOne = new Order($sheet, '[]', $datetime->modify('-5 day'));
        $orderOne->addRow(new Order\Row($orderOne, 1, 20, $plan));
        $orderOne->addRow(new Order\Row($orderOne, 2, 20, $participant));
        $orderOne->addRow(new Order\Row($orderOne, 1, 20, $option));
        $sheet->addOrder($orderOne);

        $orderTwo = new Order($sheet, '[]', $datetime->modify('-2 day'));
        $orderTwo->addRow(new Order\Row($orderTwo, -1, 20, $participant));
        $orderTwo->addRow(new Order\Row($orderTwo, 3, 20, $option));
        $sheet->addOrder($orderTwo);

        $mergedOrder = new Order($sheet, '[]', $datetime->modify('-5 day'));
        $mergedOrder->addRow(new Order\Row($mergedOrder, 1, 20, $plan));
        $mergedOrder->addRow(new Order\Row($mergedOrder, 1, 20, $participant));
        $mergedOrder->addRow(new Order\Row($mergedOrder, 4, 20, $option));

        $merger = $this->prophesize(Merger::class);
        $merger->merge(
            [
                $orderOne,
                $orderTwo,
            ]
        )->shouldBeCalled()->willReturn($mergedOrder)
        ;

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository
            ->findNotCancelledAndNotInvoicedBySheet($sheet)
            ->shouldBeCalled()
            ->willReturn(
                [
                    $orderOne,
                    $orderTwo,
                ]
            )
        ;

        $billingInfosView = $this->prophesize(BillingInfosView::class);
        $summaryView = $this->prophesize(SummaryView::class);
        $vatListView = new VatListView(10000, 12000, true, 'et', []);
        $invoiceDataView = new InvoiceDataView(
            $summaryView->reveal(),
            $billingInfosView->reveal(),
            $vatListView,
            1200
        );

        $invoiceDataQueryHandler = $this->prophesize(InvoiceDataQueryHandler::class);
        $invoiceDataQueryHandler
            ->handle(new InvoiceDataQuery($sheet, $mergedOrder, 'fr'))
            ->shouldBeCalled()
            ->willReturn($invoiceDataView)
        ;

        $serializerAdapter = $this->prophesize(SerializerAdapterInterface::class);
        $serializerAdapter
            ->serialize($invoiceDataView, 'json')
            ->shouldBeCalled()
            ->willReturn('serialized_invoice_data')
        ;

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            $merger->reveal(),
            $invoiceDataQueryHandler->reveal(),
            $serializerAdapter->reveal()
        );

        $expectedOrdersToInvoiceView = new OrdersToInvoiceView(
            [
                $orderOne,
                $orderTwo,
            ],
            'serialized_invoice_data',
            true,
            'et',
            20,
            10000,
            2000,
            12000,
            'EUR'
        );

        $this->assertEquals($expectedOrdersToInvoiceView, $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet));
    }

    public function testGetInvoiceViewForSheetWithNoOrder()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([]);

        $merger = $this->prophesize(Merger::class);

        $invoiceDataQueryHandler = $this->prophesize(InvoiceDataQueryHandler::class);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            $merger->reveal(),
            $invoiceDataQueryHandler->reveal(),
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );

        $ordersToInvoiceView = $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet);

        $this->assertNull($ordersToInvoiceView);
    }

    public function testGetInvoiceViewForSheetWithNegativeOrder()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $option = Product::createOption($event, 'option', '', 169, 20, 50, 10, 20, true);

        $orderNegative = new Order($sheet, '[]', $datetime->modify('-5 day'));
        $orderNegative->addRow(new Order\Row($orderNegative, -1, 20, $option));
        $sheet->addOrder($orderNegative);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([$orderNegative]);

        $merger = $this->prophesize(Merger::class);
        $merger->merge([$orderNegative])->willReturn($orderNegative);

        $invoiceDataQueryHandler = $this->prophesize(InvoiceDataQueryHandler::class);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            $merger->reveal(),
            $invoiceDataQueryHandler->reveal(),
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );

        $ordersToInvoiceView = $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet);

        $this->assertNull($ordersToInvoiceView);
    }
}
