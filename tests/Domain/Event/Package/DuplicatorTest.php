<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\Package\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Package\Duplicator as PackageDuplicator;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $fromEvent = $this->prophesize(Event::class);
        $toEvent   = $this->prophesize(Event::class);
        $package1 = $this->prophesize(Package::class);
        $package2 = $this->prophesize(Package::class);
        $product = $this->prophesize(Product::class);
        $products = [1 => $product->reveal()];

        $toEvent->getDuplicatedFrom()->willReturn($fromEvent->reveal());

        $newPackage1 = $this->prophesize(Package::class);
        $newPackage2 = $this->prophesize(Package::class);

        $package1->getId()->willReturn(12);
        $package2->getId()->willReturn(14);
        $packageDuplicator = $this->prophesize(PackageDuplicator::class);
        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);

        $packageRepository
            ->findByEvent($fromEvent->reveal())
            ->shouldBeCalled()
            ->willReturn([$package1->reveal(), $package2->reveal()])
        ;

        $packageDuplicator
            ->duplicatePackageWithCorrespondingProducts($toEvent->reveal(), $package1->reveal(), $products)
            ->shouldBeCalled()
            ->willReturn($newPackage1->reveal())
        ;
        $packageDuplicator
            ->duplicatePackageWithCorrespondingProducts($toEvent->reveal(), $package2->reveal(), $products)
            ->shouldBeCalled()
            ->willReturn($newPackage2->reveal())
        ;

        $packageRepository->add($newPackage1)->shouldBeCalled();
        $packageRepository->add($newPackage2)->shouldBeCalled();

        $storage = new DuplicatorDataStorage();
        $storage->products = $products;

        $duplicator = new Duplicator($packageDuplicator->reveal(), $packageRepository->reveal());
        $result = $duplicator->duplicate($toEvent->reveal(), $storage);

        $expected = new DuplicatorDataStorage();
        $expected->products = $products;
        $expected->packageTemplates = [
            12 => $newPackage1->reveal(),
            14 => $newPackage2->reveal(),
        ];

        $this->assertEquals($expected, $result);
    }
}
