<?php

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\PackageProductsNeededByHappening;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;

class PackageProductsNeededByHappeningTest extends TestCase
{
    private $packageProductsNeededByHappening;
    private $product1;
    private $product2;
    private $product3;
    private $product4;
    private $package;
    private $happening;

    public function setUp()
    {
        $this->product1 = $this->prophesize(Product::class);
        $this->product1->getId()->willReturn(1);

        $this->product2 = $this->prophesize(Product::class);
        $this->product2->getId()->willReturn(2);

        $this->product3 = $this->prophesize(Product::class);
        $this->product3->getId()->willReturn(3);

        $this->product4 = $this->prophesize(Product::class);
        $this->product4->getId()->willReturn(4);

        $this->package = $this->prophesize(Package::class);

        $this->happening = $this->prophesize(Happening::class);

        $this->packageProductsNeededByHappening = new PackageProductsNeededByHappening();
    }

    public function testPackageIsNotPassable()
    {
        $this->package->isPassable()->shouldBeCalled()->willReturn(false);

        $this->assertEquals(
            [],
            $this->packageProductsNeededByHappening->get($this->package->reveal(), $this->happening->reveal())
        );
    }

    public function testHappeningHasNoProduct()
    {
        $this->package->isPassable()->shouldBeCalled()->willReturn(true);
        $this->happening->hasProducts()->shouldBeCalled()->willReturn(false);

        $this->assertEquals(
            [],
            $this->packageProductsNeededByHappening->get($this->package->reveal(), $this->happening->reveal())
        );
    }

    public function testNoMatchingProduct()
    {
        $this->happening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->happening->getProducts()->shouldBeCalled()->willReturn(
            [
                $this->product1->reveal(),
                $this->product2->reveal(),
            ]
        );

        $this->package->isPassable()->shouldBeCalled()->willReturn(true);
        $this->package->getOptions()->shouldBeCalled()->willReturn([$this->product3->reveal()]);

        $this->assertEquals(
            [],
            $this->packageProductsNeededByHappening->get($this->package->reveal(), $this->happening->reveal())
        );
    }

    public function testMatchingProduct()
    {
        $this->happening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->happening->getProducts()->shouldBeCalled()->willReturn(
            [
                $this->product1->reveal(),
                $this->product2->reveal(),
                $this->product3->reveal(),
            ]
        );

        $this->package->isPassable()->shouldBeCalled()->willReturn(true);
        $this->package->getOptions()->shouldBeCalled()->willReturn(
            [
                $this->product2->reveal(),
                $this->product3->reveal(),
                $this->product4->reveal(),
            ]
        );

        $this->assertEquals(
            [$this->product2->reveal(), $this->product3->reveal()],
            $this->packageProductsNeededByHappening->get($this->package->reveal(), $this->happening->reveal())
        );
    }
}
