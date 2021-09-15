<?php

namespace Proximum\Vimeet\Tests\Domain\Product;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class RemoveAuthorizationCheckerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $productRepository;

    public function setUp()
    {
        $this->productRepository = $this->prophesize(ProductRepositoryInterface::class);
    }

    public function testCanBeRemovedPreload()
    {
        // Context
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(1);
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);
        $product1->getEvent()->willReturn($event->reveal());
        $product2->getEvent()->willReturn($event->reveal());
        $product1->getId()->willReturn(12);
        $product2->getId()->willReturn(15);

        // Mock
        $this->productRepository
            ->findRemovableProductsForEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([12 => $product1->reveal()]);

        $removeAuthorizationChecker = new RemoveAuthorizationChecker($this->productRepository->reveal());
        $removeAuthorizationChecker->preloadForEvent($event->reveal());

        $this->assertTrue($removeAuthorizationChecker->canBeRemoved($product1->reveal()));
        $this->assertFalse($removeAuthorizationChecker->canBeRemoved($product2->reveal()));
    }

    public function testCanBeRemoved()
    {
        // Context
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(1);
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);
        $product1->getEvent()->willReturn($event->reveal());
        $product2->getEvent()->willReturn($event->reveal());
        $product1->getId()->willReturn(12);
        $product2->getId()->willReturn(15);

        // Mock
        $this->productRepository->isProductRemovable($product1->reveal())->shouldBeCalled()->willReturn(true);
        $this->productRepository->isProductRemovable($product2->reveal())->shouldBeCalled()->willReturn(false);

        $removeAuthorizationChecker = new RemoveAuthorizationChecker($this->productRepository->reveal());

        $this->assertTrue($removeAuthorizationChecker->canBeRemoved($product1->reveal()));
        $this->assertFalse($removeAuthorizationChecker->canBeRemoved($product2->reveal()));
    }
}
