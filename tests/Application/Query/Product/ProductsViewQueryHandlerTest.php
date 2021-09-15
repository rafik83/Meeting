<?php

namespace Proximum\Vimeet\Tests\Application\Query\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Product\ProductsViewQuery;
use Proximum\Vimeet\Application\Query\Product\ProductsViewQueryHandler;
use Proximum\Vimeet\Application\View\Product\ProductView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductsViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);
        $product3 = $this->prophesize(Product::class);
        $includedProduct = $this->prophesize(Product\ProductIncluded::class);
        $includedProduct->getQuantity()->willReturn(4);
        $includedProduct->getIncluded()->willReturn($product3->reveal());
        $date = new \DateTime();
        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);
        $product3->getId()->willReturn(3);
        $product1->hasHappenings()->willReturn(false);
        $product2->hasHappenings()->willReturn(false);
        $product3->hasHappenings()->willReturn(true);
        $product1->getName()->willReturn('name 1');
        $product2->getName()->willReturn('name 2');
        $product3->getName()->willReturn('name 3');
        $product1->getType()->willReturn('plan');
        $product2->getType()->willReturn('participant');
        $product3->getType()->willReturn('planning');
        $product1->isPlan()->willReturn(true);
        $product2->isPlan()->willReturn(false);
        $product3->isPlan()->willReturn(false);
        $product1->getUnitPrice()->willReturn(123.99);
        $product2->getUnitPrice()->willReturn(677213);
        $product3->getUnitPrice()->willReturn(2339.33);
        $product1->isSubjectedToValidation()->willReturn(false);
        $product2->isSubjectedToValidation()->willReturn(true);
        $product3->isSubjectedToValidation()->willReturn(false);
        $product1->getIncludedProducts()->willReturn([$includedProduct->reveal()]);
        $product2->getIncludedProducts()->willReturn([]);
        $product3->getIncludedProducts()->willReturn([]);
        $product1->hasIncludedProducts()->willReturn(true);
        $product2->hasIncludedProducts()->willReturn(false);
        $product3->hasIncludedProducts()->willReturn(false);
        $product1->getAvailabilityStatus(4)->willReturn('default');
        $product2->getAvailabilityStatus(9)->willReturn('warning');
        $product3->getAvailabilityStatus(0)->willReturn('alert');
        $product1->isAvailabilityManaged()->willReturn(false);
        $product2->isAvailabilityManaged()->willReturn(false);
        $product3->isAvailabilityManaged()->willReturn(true);
        $product1->isUpdatable()->willReturn(true);
        $product2->isUpdatable()->willReturn(true);
        $product3->isUpdatable()->willReturn(false);
        $product1->getQuantityMax()->willReturn(INF);
        $product2->getQuantityMax()->willReturn(9);
        $product3->getQuantityMax()->willReturn(100);
        $product1->getAvailabilityCurrent()->willReturn(null);
        $product2->getAvailabilityCurrent()->willReturn(null);
        $product3->getAvailabilityCurrent()->willReturn(8);
        $product1->getAvailabilityMax()->willReturn(23);
        $product2->getAvailabilityMax()->willReturn(null);
        $product3->getAvailabilityMax()->willReturn(123123);
        $product1->getBuyableUntil()->willReturn($date);
        $product3->getBuyableUntil()->willReturn(null);
        $product2->getBuyableUntil()->willReturn(null);
        $product1->getDeletableUntil()->willReturn(null);
        $product2->getDeletableUntil()->willReturn(null);
        $product3->getDeletableUntil()->willReturn($date);
        $product1->hasAvailabilityTimeRanges()->willReturn(false);
        $product2->hasAvailabilityTimeRanges()->willReturn(true);
        $product3->hasAvailabilityTimeRanges()->willReturn(false);
        $product1->isAttributable()->willReturn(false);
        $product2->isAttributable()->willReturn(false);
        $product3->isAttributable()->willReturn(true);

        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $removeAuthorizationChecker = $this->prophesize(RemoveAuthorizationChecker::class);
        $rowRepository = $this->prophesize(RowRepositoryInterface::class);

        $productRepository
            ->findByEventOrderedByProductTypeAndProductName($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $product1->reveal(),
                $product2->reveal(),
                $product3->reveal(),
            ])
        ;
        $rowRepository
            ->boughtByProduct($product1->reveal())
            ->shouldBeCalled()
            ->willReturn(4)
        ;
        $rowRepository
            ->boughtByProduct($product2->reveal())
            ->shouldBeCalled()
            ->willReturn(9)
        ;
        $rowRepository
            ->boughtByProduct($product3->reveal())
            ->shouldBeCalled()
            ->willReturn(0)
        ;
        $removeAuthorizationChecker->preloadForEvent($event->reveal())->shouldBeCalled();
        $removeAuthorizationChecker->canBeRemoved($product1->reveal())->shouldBeCalled()->willReturn(true);
        $removeAuthorizationChecker->canBeRemoved($product2->reveal())->shouldBeCalled()->willReturn(false);
        $removeAuthorizationChecker->canBeRemoved($product3->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new ProductsViewQueryHandler(
            $productRepository->reveal(),
            $rowRepository->reveal(),
            $removeAuthorizationChecker->reveal()
        );
        $result = $handler->handle(new ProductsViewQuery($event->reveal()));

        $expected = [
            new ProductView(
                1,
                'name 1',
                'plan',
                123.99,
                false,
                [
                    [
                        'quantity' => 4,
                        'name' => 'name 3',
                        'type' => 'planning',
                    ],
                ],
                4,
                0,
                true,
                'default',
                false,
                true,
                INF,
                null,
                23,
                $date,
                null,
                false,
                false,
                false
            ),
            new ProductView(
                2,
                'name 2',
                'participant',
                677213,
                true,
                [],
                9,
                0,
                false,
                'warning',
                false,
                true,
                9,
                null,
                null,
                null,
                null,
                true,
                false,
                false
            ),
            new ProductView(
                3,
                'name 3',
                'planning',
                2339.33,
                false,
                [],
                0,
                16,
                true,
                'alert',
                true,
                false,
                100,
                8,
                123123,
                null,
                $date,
                false,
                true,
                true
            ),
        ];

        $this->assertEquals($expected, $result);
    }
}
