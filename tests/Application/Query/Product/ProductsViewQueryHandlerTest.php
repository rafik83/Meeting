<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Product\ProductsViewQuery;
use Proximum\Vimeet\Application\Query\Product\ProductsViewQueryHandler;
use Proximum\Vimeet\Application\View\Product\ProductView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductsViewQueryHandlerTest extends TestCase
{
    public function testHandle()
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
        $product1->getName()->willReturn('name 1');
        $product2->getName()->willReturn('name 2');
        $product3->getName()->willReturn('name 3');
        $product1->getType()->willReturn('plan');
        $product2->getType()->willReturn('participant');
        $product3->getType()->willReturn('planning');
        $product1->getUnitPrice()->willReturn(123.99);
        $product2->getUnitPrice()->willReturn(677213);
        $product3->getUnitPrice()->willReturn(2339.33);
        $product1->isSubjectedToValidation()->willReturn(false);
        $product2->isSubjectedToValidation()->willReturn(true);
        $product3->isSubjectedToValidation()->willReturn(false);
        $product1->getIncludedProducts()->willReturn([$includedProduct->reveal()]);
        $product2->getIncludedProducts()->willReturn([]);
        $product3->getIncludedProducts()->willReturn([]);
        $product1->getAvailabilityStatus(4)->willReturn('default');
        $product2->getAvailabilityStatus(9)->willReturn('warning');
        $product3->getAvailabilityStatus(0)->willReturn('alert');
        $product1->isAvailabilityManaged()->willReturn(false);
        $product2->isAvailabilityManaged()->willReturn(false);
        $product3->isAvailabilityManaged()->willReturn(true);
        $product1->isUpdatable()->willReturn(true);
        $product2->isUpdatable()->willReturn(true);
        $product3->isUpdatable()->willReturn(false);
        $product1->getQuantityMax()->willReturn(null);
        $product2->getQuantityMax()->willReturn(9);
        $product3->getQuantityMax()->willReturn(100);
        $product1->getAvailabilityCurrent()->willReturn(null);
        $product2->getAvailabilityCurrent()->willReturn(null);
        $product3->getAvailabilityCurrent()->willReturn(8);
        $product1->getAvailabilityMax()->willReturn(23);
        $product2->getAvailabilityMax()->willReturn(null);
        $product3->getAvailabilityMax()->willReturn(123123);
        $product1->getBuyableUntil()->WillReturn($date);
        $product3->getBuyableUntil()->WillReturn(null);
        $product2->getBuyableUntil()->WillReturn(null);
        $product1->getDeletableUntil()->willReturn(null);
        $product2->getDeletableUntil()->willReturn(null);
        $product3->getDeletableUntil()->willReturn($date);

        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $removeAuthorizationChecker = $this->prophesize(RemoveAuthorizationChecker::class);

        $productRepository
            ->countByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                [0 => $product1->reveal(), 'bought' => 4],
                [0 => $product2->reveal(), 'bought' => 9],
                [0 => $product3->reveal(), 'bought' => null],
            ])
        ;
        $removeAuthorizationChecker->preloadForEvent($event->reveal())->shouldBeCalled();
        $removeAuthorizationChecker->canBeRemoved($product1->reveal())->shouldBeCalled()->willReturn(true);
        $removeAuthorizationChecker->canBeRemoved($product2->reveal())->shouldBeCalled()->willReturn(false);
        $removeAuthorizationChecker->canBeRemoved($product3->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new ProductsViewQueryHandler($productRepository->reveal(), $removeAuthorizationChecker->reveal());
        $result = $handler->handle(new ProductsViewQuery($event->reveal()));

        $expected = [
            new ProductView(
                1,
                'name 1',
                'plan',
                123.99,
                false,
                [0 => ['quantity' => 4, 'name' => 'name 3']],
                4,
                true,
                'default',
                false,
                true,
                null,
                null,
                23,
                $date,
                null
            ),
            new ProductView(
                2,
                'name 2',
                'participant',
                677213,
                true,
                [],
                9,
                false,
                'warning',
                false,
                true,
                9,
                null,
                null,
                null,
                null
            ),
            new ProductView(
                3,
                'name 3',
                'planning',
                2339.33,
                false,
                [],
                0,
                true,
                'alert',
                true,
                false,
                100,
                8,
                123123,
                null,
                $date
            ),
        ];

        $this->assertEquals($expected, $result);
    }
}
