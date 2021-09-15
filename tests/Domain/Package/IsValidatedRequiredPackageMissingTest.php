<?php


namespace Proximum\Vimeet\Tests\Domain\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Package\IsValidatedRequiredPackageMissing;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class IsValidatedRequiredPackageMissingTest extends TestCase
{
    public function testTypeNotConcerned(): void
    {
        // prepare data
        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);

        $sheet->getType()->willReturn($type->reveal());
        $type->isPackageRequired()->willReturn(false);

        // prophecy dependencies
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        // run tests
        $isValidatedRequiredPackageMissing = new IsValidatedRequiredPackageMissing($orderRepository->reveal());
        $result = $isValidatedRequiredPackageMissing->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }

    public function testNotMissing(): void
    {
        // prepare data
        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);
        $order = $this->prophesize(Order::class);

        $sheet->getType()->willReturn($type->reveal());
        $type->isPackageRequired()->willReturn(true);

        // prophecy dependencies
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $orderRepository->findNotCancelledBySheet($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$order->reveal()]);

        // run tests
        $isValidatedRequiredPackageMissing = new IsValidatedRequiredPackageMissing($orderRepository->reveal());
        $result = $isValidatedRequiredPackageMissing->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }

    public function testMissing(): void
    {
        // prepare data
        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);

        $sheet->getType()->willReturn($type->reveal());
        $type->isPackageRequired()->willReturn(true);

        // prophecy dependencies
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $orderRepository->findNotCancelledBySheet($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        // run tests
        $isValidatedRequiredPackageMissing = new IsValidatedRequiredPackageMissing($orderRepository->reveal());
        $result = $isValidatedRequiredPackageMissing->isSatisfiedBy($sheet->reveal());

        $this->assertTrue($result);
    }
}
