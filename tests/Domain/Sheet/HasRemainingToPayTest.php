<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Sheet\HasRemainingToPay;

class HasRemainingToPayTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $package;

    /** @var ObjectProphecy */
    private $balance;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->package = $this->prophesize(Package::class);
        $this->balance = $this->prophesize(Balance::class);
    }

    public function testIsSatisfiedByPackageNotPassable(): void
    {
        $this->sheet->getPackage()->willReturn($this->package->reveal());
        $this->package->isPassable()->shouldBeCalled()->willReturn(false);

        $this->balance->getRemainingToPay($this->sheet->reveal())->shouldNotBeCalled();

        $hasRemainingToPay = new HasRemainingToPay(
            $this->balance->reveal()
        );

        $result = $hasRemainingToPay->isSatisfiedBy($this->sheet->reveal());

        $this->assertFalse($result);
    }

    public function testIsSatisfiedByWithoutOrder(): void
    {
        $this->sheet->getPackage()->willReturn($this->package->reveal());
        $this->package->isPassable()->shouldBeCalled()->willReturn(true);

        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);
        $this->balance->getRemainingToPay($this->sheet->reveal())->shouldNotBeCalled();

        $hasRemainingToPay = new HasRemainingToPay(
            $this->balance->reveal()
        );

        $result = $hasRemainingToPay->isSatisfiedBy($this->sheet->reveal());

        $this->assertTrue($result);
    }

    public function testIsSatisfiedByWithRemainingToPay(): void
    {
        $this->sheet->getPackage()->willReturn($this->package->reveal());
        $this->package->isPassable()->shouldBeCalled()->willReturn(true);

        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(true);

        $this->balance->getRemainingToPay($this->sheet->reveal())->shouldBeCalled()->willReturn(999);

        $hasRemainingToPay = new HasRemainingToPay(
            $this->balance->reveal()
        );

        $result = $hasRemainingToPay->isSatisfiedBy($this->sheet->reveal());

        $this->assertTrue($result);
    }

    public function testIsSatisfiedByWithoutRemainingToPay(): void
    {
        $this->sheet->getPackage()->willReturn($this->package->reveal());
        $this->package->isPassable()->shouldBeCalled()->willReturn(true);

        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(true);
        $this->balance->getRemainingToPay($this->sheet->reveal())->shouldBeCalled()->willReturn(0);

        $hasRemainingToPay = new HasRemainingToPay(
            $this->balance->reveal()
        );

        $result = $hasRemainingToPay->isSatisfiedBy($this->sheet->reveal());

        $this->assertFalse($result);
    }
}
