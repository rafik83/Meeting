<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Order\Export\BillingInfoViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\BillingInfoViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowBoughtViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\OrderViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\OrderViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\ProductBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\ProductBoughtViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeBoughtViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\VatListViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\VatListViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\BillingInfoView;
use Proximum\Vimeet\Application\View\Order\Export\CustomRowBoughtView;
use Proximum\Vimeet\Application\View\Order\Export\OrderView;
use Proximum\Vimeet\Application\View\Order\Export\ProductBoughtView;
use Proximum\Vimeet\Application\View\Order\Export\PromotionCodeBoughtView;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Application\View\Package\Vat\VatView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class OrderViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $date    = new \DateTime('2016-10-12');
        $order   = $this->prophesize(Order::class);
        $sheet   = $this->prophesize(Sheet::class);
        $event   = EventFactory::createEvent();
        $prefix  = new Prefix('prefix title', 'prefix');
        $invoice = new Invoice(
            $event,
            $sheet->reveal(),
            $prefix,
            'invoicePrefix',
            2017,
            1,
            false,
            'vatMode',
            10,
            1000,
            1000,
            0,
            'euro',
            '',
            $date
        );

        $order->getId()->willReturn(2);
        $order->getSheet()->willReturn($sheet->reveal());
        $sheet->getId()->willReturn(3);
        $order->getCreatedAt()->willReturn(new \DateTime('2016-10-12'));

        $row1 = $this->prophesize(Order\Row::class);
        $row1->isProduct()->willReturn(true);

        $row2 = $this->prophesize(Order\Row::class);
        $row2->isProduct()->willReturn(false);

        $promotionCode = $this->prophesize(Order\PromotionCode::class);

        $order->getRows()->willReturn([$row1->reveal(), $row2->reveal()]);
        $order->getInvoice()->shouldBeCalled()->willReturn($invoice);
        $order->hasInvoice()->shouldBeCalled()->willReturn(true);
        $order->getPromotionCodes()->willReturn([$promotionCode->reveal()]);

        $locale                              = 'en';
        $adminLocale                         = 'fr';
        $sheetInfoGuesserCache               = $this->prophesize(SheetInfoGuesserCache::class);
        $billingInfoViewQueryHandler         = $this->prophesize(BillingInfoViewQueryHandler::class);
        $productBoughtViewQueryHandler       = $this->prophesize(ProductBoughtViewQueryHandler::class);
        $customRowBoughtViewQueryHandler     = $this->prophesize(CustomRowBoughtViewQueryHandler::class);
        $promotionCodeBoughtViewQueryHandler = $this->prophesize(PromotionCodeBoughtViewQueryHandler::class);

        $sheetInfoGuesserCache->guessSheetTitle($sheet->reveal(), $locale)->shouldBeCalled()->willReturn('sheet title');
        $billingInfo = new BillingInfoView(
            'gender',
            'lastName',
            'firstName',
            'position',
            'phone',
            'mobile',
            'email@email.fr'
        );
        $billingInfo->countryCode = 'FR';

        $productBought = new ProductBoughtView(1, 2, 3, 6);
        $productBoughtViewQueryHandler->handle(new ProductBoughtViewQuery($row1->reveal()))
            ->shouldBeCalled()
            ->willReturn($productBought);
        $customRowBought = new CustomRowBoughtView(2, 'title', 23, 2, 46);
        $customRowBoughtViewQueryHandler->handle(new CustomRowBoughtViewQuery($row2->reveal()))
            ->shouldBeCalled()
            ->willReturn($customRowBought);

        $promotionCodeBought = new PromotionCodeBoughtView(2, 1, 120);
        $promotionCodeBoughtViewQueryHandler->handle(new PromotionCodeBoughtViewQuery($promotionCode->reveal()))
            ->shouldBeCalled()
            ->willReturn($promotionCodeBought);

        $billingInfoViewQueryHandler->handle(new BillingInfoViewQuery($sheet->reveal(), $adminLocale))
            ->shouldBeCalled()
            ->willReturn($billingInfo);

        $vatViews = [
            'vat_20' => new VatView(20, Event::VAT_MODE_ATI, 50000, 10000),
            'vat_10' => new VatView(10, Event::VAT_MODE_ATI, 25000, 2500),
        ];
        $vatListView = new VatListView(75000, 87500.0, true, Event::VAT_MODE_ATI, $vatViews);
        $vatListViewQueryHandler = $this->prophesize(VatListViewQueryHandler::class);
        $vatListViewQueryHandler->handle(new VatListViewQuery($order->reveal(), true))
            ->shouldBeCalled()
            ->willReturn($vatListView)
        ;

        $vatApplicable = $this->prophesize(VatApplicable::class);
        $vatApplicable->isApplicable(
                Event::VAT_MODE_ET,
                'FR',
                'FR',
                null
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $handler = new OrderViewQueryHandler(
            $sheetInfoGuesserCache->reveal(),
            $billingInfoViewQueryHandler->reveal(),
            $productBoughtViewQueryHandler->reveal(),
            $customRowBoughtViewQueryHandler->reveal(),
            $promotionCodeBoughtViewQueryHandler->reveal(),
            $vatListViewQueryHandler->reveal(),
            $vatApplicable->reveal()
        );
        $result  = $handler->handle(new OrderViewQuery($event, $order->reveal(), $locale, $adminLocale));

        $expected = new OrderView(
            2,
            '10/12/16',
            3,
            'sheet title',
            'invoicePrefix2017-0001',
            '10/12/16',
            $billingInfo,
            750,
            125,
            875,
            [$productBought],
            [$promotionCodeBought],
            [$customRowBought]
        );

        $this->assertEquals($expected, $result);
    }
}
