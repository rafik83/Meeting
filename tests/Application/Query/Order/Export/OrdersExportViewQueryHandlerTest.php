<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\OrdersExportViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\OrdersExportViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\OrderViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\OrderViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\ProductViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\ProductViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\SharedColumnsTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\SharedColumnsTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\CustomRowView;
use Proximum\Vimeet\Application\View\Order\Export\OrdersExportView;
use Proximum\Vimeet\Application\View\Order\Export\OrderView;
use Proximum\Vimeet\Application\View\Order\Export\ProductView;
use Proximum\Vimeet\Application\View\Order\Export\PromotionCodeView;
use Proximum\Vimeet\Application\View\Order\Export\SharedColumnsTranslationView;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class OrdersExportViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event       = EventFactory::createEvent();
        $adminLocale = 'en';
        $orderRepository                          = $this->prophesize(OrderRepositoryInterface::class);
        $productRepository                        = $this->prophesize(ProductRepositoryInterface::class);
        $promotionCodeRepository                  = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $productViewQueryHandler                  = $this->prophesize(ProductViewQueryHandler::class);
        $promotionCodeViewQueryHandler            = $this->prophesize(PromotionCodeViewQueryHandler::class);
        $orderViewQueryHandler                    = $this->prophesize(OrderViewQueryHandler::class);
        $customRowViewQueryHandler                = $this->prophesize(CustomRowViewQueryHandler::class);
        $sharedColumnsTranslationViewQueryHandler = $this->prophesize(SharedColumnsTranslationViewQueryHandler::class);
        $sharedColumns = $this->prophesize(SharedColumnsTranslationView::class);
        $sharedColumnsTranslationViewQueryHandler->handle(new SharedColumnsTranslationViewQuery($adminLocale))->shouldBeCalled()->willReturn($sharedColumns->reveal());

        $order1 = $this->prophesize(Order::class);
        $order2 = $this->prophesize(Order::class);
        $orderRepository->findNotCancelledWithJoinRowAndPromotionCodeByEvent($event)->shouldBeCalled()->willReturn([$order1->reveal(), $order2->reveal()]);

        $orderView1 = $this->prophesize(OrderView::class);
        $orderView2 = $this->prophesize(OrderView::class);
        $orderView1->countCustomRows()->willReturn(1);
        $orderView2->countCustomRows()->willReturn(2);

        $orderViewQueryHandler->preloadBillingInfo($event)->shouldBeCalled();
        $orderViewQueryHandler->handle(new OrderViewQuery($event, $order1->reveal(), 'fr', $adminLocale))->shouldBeCalled()->willReturn($orderView1->reveal());
        $orderViewQueryHandler->handle(new OrderViewQuery($event, $order2->reveal(), 'fr', $adminLocale))->shouldBeCalled()->willReturn($orderView2->reveal());

        $product = $this->prophesize(Product::class);
        $product->isPlan()->willReturn(false);
        $product->isParticipant()->willReturn(false);
        $product->isPlanning()->willReturn(false);
        $product->isOption()->willReturn(true);
        $productRepository->findProductsBoughtByEvent($event)->shouldBeCalled()->willReturn([$product->reveal()]);

        $productView = $this->prophesize(ProductView::class);
        $productViewQueryHandler->handle(new ProductViewQuery($product->reveal(), 'fr', $adminLocale))->shouldBeCalled()->willReturn($productView->reveal());

        $promotionCode = $this->prophesize(PromotionCode::class);
        $promotionCodeRepository->findBoughtByEvent($event)->shouldBeCalled()->willReturn([$promotionCode->reveal()]);
        $promotionCodeView = $this->prophesize(PromotionCodeView::class);
        $promotionCodeViewQueryHandler->handle(new PromotionCodeViewQuery($promotionCode->reveal(), $adminLocale))->shouldBeCalled()->willReturn($promotionCodeView->reveal());

        $customRowView  = $this->prophesize(CustomRowView::class);
        $customRowView2 = $this->prophesize(CustomRowView::class);
        $customRowViewQueryHandler->handle(new CustomRowViewQuery(1, $adminLocale))->shouldBeCalled()->willReturn($customRowView->reveal());
        $customRowViewQueryHandler->handle(new CustomRowViewQuery(2, $adminLocale))->shouldBeCalled()->willReturn($customRowView2->reveal());

        $handler = new OrdersExportViewQueryHandler(
            $orderRepository->reveal(),
            $productRepository->reveal(),
            $promotionCodeRepository->reveal(),
            $productViewQueryHandler->reveal(),
            $promotionCodeViewQueryHandler->reveal(),
            $orderViewQueryHandler->reveal(),
            $customRowViewQueryHandler->reveal(),
            $sharedColumnsTranslationViewQueryHandler->reveal()
        );

        $query = new OrdersExportViewQuery($event, $adminLocale);
        $result = $handler->handle($query);

        $expected = new OrdersExportView(
            $sharedColumns->reveal(),
            [$productView->reveal()],
            [$promotionCodeView->reveal()],
            [$orderView1->reveal(), $orderView2->reveal()],
            [$customRowView->reveal(), $customRowView2->reveal()]
        );

        $this->assertEquals($expected, $result);
    }
}
