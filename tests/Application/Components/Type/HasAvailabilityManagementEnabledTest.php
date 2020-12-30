<?php

namespace Proximum\Vimeet\Tests\Application\Components\Type;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Components\Type\HasAvailabilityManagementEnabled;
use Proximum\Vimeet\Application\Components\Type\HasUnavailabilityManagementDisabled;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class HasAvailabilityManagementEnabledTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheet, $type;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet->getType()->shouldBeCalled()->willReturn($this->type->reveal());
    }

    public function testTypeNone(): void
    {
        $this->type->getAvailabilityType()->shouldBeCalled()->willReturn(Type::TYPE_MANAGEMENT_NONE);

        $service = new HasAvailabilityManagementEnabled();
        $this->assertFalse($service->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testTypeAvailable(): void
    {
        $this->type->getAvailabilityType()->shouldBeCalled()->willReturn(Type::TYPE_MANAGEMENT_AVAILABLE);

        $service = new HasAvailabilityManagementEnabled();
        $this->assertTrue($service->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testTypeUnavailable(): void
    {
        $this->type->getAvailabilityType()->shouldBeCalled()->willReturn(Type::TYPE_MANAGEMENT_UNAVAILABLE);

        $service = new HasAvailabilityManagementEnabled();
        $this->assertFalse($service->isSatisfiedBy($this->sheet->reveal()));
    }
}
