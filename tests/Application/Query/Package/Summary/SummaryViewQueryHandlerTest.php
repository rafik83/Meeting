<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Summary;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupsViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\GroupsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\PromotionCodeQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\PromotionCodeQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\SummaryViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\SummaryViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Vat\VatListViewQuery;
use Proximum\Vimeet\Application\Query\Package\Vat\VatListViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\GroupsView;
use Proximum\Vimeet\Application\View\Package\Summary\GroupView;
use Proximum\Vimeet\Application\View\Package\Summary\ProductView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodesView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodeView;
use Proximum\Vimeet\Application\View\Package\Summary\SummaryView;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Application\View\Package\Vat\VatView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SummaryViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $datetime);
        $package->enable(true, true, true);
        $sheet   = SheetFactory::create($event, null, $datetime, $type);
        $product = ProductFactory::create($event, 'option');
        $funnel  = new Funnel($sheet);

        $package->setPlanning($product);
        $type->setPackage($package);

        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);

        // Mock
        $groupsViewQueryHandler    = $this->prophesize(GroupsViewQueryHandler::class);
        $promotionCodeQueryHandler = $this->prophesize(PromotionCodeQueryHandler::class);

        $productView1 = new ProductView(1, 'label1', 20, 1, 20, Event::VAT_MODE_ATI, 20, 'EUR');
        $productView2 = new ProductView(1, 'label1', 80, 1, 80, Event::VAT_MODE_ATI, 20, 'EUR');
        $groupView = new GroupView(
            'label',
            [$productView1, $productView2],
            100
        );
        $groupsView         = new GroupsView(null, null, [$groupView]);
        $promotionCodesView = new PromotionCodesView([new PromotionCodeView(1, '', '', 0, 'EUR', '', [])]);

        $groupsViewQueryHandler->handle(Argument::type(GroupsViewQuery::class))->shouldBeCalled()
            ->willReturn($groupsView);

        $promotionCodeQueryHandler->handle(Argument::type(PromotionCodeQuery::class))->shouldBeCalled()
            ->willReturn($promotionCodesView);

        $vatView1 = new VatView(20, Event::VAT_MODE_ATI, 100, 120);
        $vatListView = new VatListView(100, 120, true, Event::VAT_MODE_ATI, [$vatView1]);
        $vatListViewQueryHandler = $this->prophesize(VatListViewQueryHandler::class);
        $vatListViewQueryHandler
            ->handle(new VatListViewQuery($sheet, $groupsView, $promotionCodesView))
            ->shouldBeCalled()
            ->willReturn($vatListView)
        ;
        $handler = new SummaryViewQueryHandler(
            $groupsViewQueryHandler->reveal(),
            $promotionCodeQueryHandler->reveal(),
            $vatListViewQueryHandler->reveal()
        );

        // Expected
        $expectedSummaryView = new SummaryView(
            $funnel,
            $groupsView,
            $promotionCodesView,
            $event->getMode(),
            100,
            120,
            $event->getCurrency(),
            true,
            $vatListView
        );

        $query = new SummaryViewQuery($sheet, $funnel, $cart, $locale);

        $summaryView = $handler->handle($query);

        $this->assertEquals($expectedSummaryView, $summaryView);
    }
}
