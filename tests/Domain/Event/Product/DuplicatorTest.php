<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\Product\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\Duplicator as ProductDuplicator;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $fromEvent = $this->prophesize(Event::class);
        $toEvent = $this->prophesize(Event::class);
        $toEvent->getDuplicatedFrom()->willReturn($fromEvent->reveal());

        $productRepository     = $this->prophesize(ProductRepositoryInterface::class);
        $productDuplicator     = $this->prophesize(ProductDuplicator::class);
        $duplicatorDataStorage = new DuplicatorDataStorage();

        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);
        $product3 = $this->prophesize(Product::class);
        $fromProducts = [4, 10, 54];
        $products = [
            4 => $product1->reveal(),
            10 => $product2->reveal(),
            54 => $product3->reveal(),
        ];

        $productRepository->findByEvent($fromEvent->reveal())->shouldBeCalled()->willReturn($fromProducts);
        $productRepository->add($product1->reveal())->shouldBeCalled();
        $productRepository->add($product2->reveal())->shouldBeCalled();
        $productRepository->add($product3->reveal())->shouldBeCalled();
        $productDuplicator
            ->duplicateProducts($toEvent->reveal(), $fromProducts)
            ->shouldBeCalled()
            ->willReturn($products)
        ;

        $duplicator = new Duplicator($productDuplicator->reveal(), $productRepository->reveal());

        $result = $duplicator->duplicate($toEvent->reveal(), $duplicatorDataStorage);

        $expected = new DuplicatorDataStorage();
        $expected->products = [
            4  => $product1->reveal(),
            10 => $product2->reveal(),
            54 => $product3->reveal(),
        ];

        $this->assertEquals($expected, $result);
    }
}
