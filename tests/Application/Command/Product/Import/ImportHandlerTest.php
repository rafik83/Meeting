<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Import;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Product\Import\Import;
use Proximum\Vimeet\Application\Command\Product\Import\ImportHandler;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Package\Duplicator as PackageDuplicator;
use Proximum\Vimeet\Domain\Product\Duplicator;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ImportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event        = EventFactory::createEvent();
        $currentEvent = EventFactory::createEvent();

        $product  = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);
        $newProduct1 = $this->prophesize(Product::class);
        $newProduct2 = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $newPackage = $this->prophesize(Package::class);

        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);
        $productDuplicator = $this->prophesize(Duplicator::class);
        $packageDuplicator = $this->prophesize(PackageDuplicator::class);

        $productRepository->findByEvent($event)->shouldBeCalled()->willReturn([$product->reveal(), $product2->reveal()]);

        $productRepository->add($newProduct1->reveal())->shouldBeCalled();
        $productRepository->add($newProduct2->reveal())->shouldBeCalled();

        $productDuplicator
            ->duplicateProducts($event, [$product->reveal(), $product2->reveal()])
            ->shouldBeCalled()
            ->willReturn([1 => $newProduct1->reveal(), 2 => $newProduct2->reveal()])
        ;

        $packageDuplicator
            ->duplicatePackageWithCorrespondingProducts($event, $package->reveal(), [1 => $newProduct1->reveal(), 2 => $newProduct2->reveal()])
            ->shouldBeCalled()
            ->willReturn($newPackage->reveal())
        ;

        $packageRepository->findByEvent($event)->shouldBeCalled()->willReturn([$package->reveal()]);
        $packageRepository->add($newPackage->reveal())->shouldBeCalled();

        $import = new Import($currentEvent);
        $import->event = $event;

        $handler = new ImportHandler(
            $productRepository->reveal(),
            $packageRepository->reveal(),
            $productDuplicator->reveal(),
            $packageDuplicator->reveal()
        );
        $handler->handle($import);
    }
}
