<?php

namespace Application\Command\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Package\Duplicate;
use Proximum\Vimeet\Application\Command\Package\DuplicateHandler;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Package\Duplicator;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class DuplicateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $package = $this->prophesize(Package::class);
        $newPackage = $this->prophesize(Package::class);

        $command        = new Duplicate($package->reveal());
        $command->title = 'Duplicate package';

        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);
        $packageDuplicator = $this->prophesize(Duplicator::class);
        $packageDuplicator
            ->duplicatePackage($package->reveal(), 'Duplicate package')
            ->shouldBeCalled()
            ->willReturn($newPackage->reveal())
        ;
        $packageRepository->add($newPackage->reveal())->shouldBeCalled();

        $handler = new DuplicateHandler(
            $packageRepository->reveal(),
            $packageDuplicator->reveal()
        );
        $handler->handle($command);
    }
}
