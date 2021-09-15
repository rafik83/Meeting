<?php

namespace Proximum\Vimeet\Tests\Application\Components\Type;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Components\Type\HasUnavailabilityManagementDisabled;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class HasUnavailabilityManagementDisabledTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheet, $type, $authorizationCheckerAdapter;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet->getType()->shouldBeCalled()->willReturn($this->type->reveal());
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
    }

    public function testIsDisabledAndImpersonate(): void
    {
        $this->type->getAvailabilityType()->shouldBeCalled()->willReturn(Type::TYPE_MANAGEMENT_NONE);
        $this->authorizationCheckerAdapter->isGranted('ROLE_PREVIOUS_ADMIN')->shouldBeCalled()->willReturn(true);

        $service = new HasUnavailabilityManagementDisabled($this->authorizationCheckerAdapter->reveal());
        $this->assertFalse($service->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testIsDisabledAndNotImpersonate(): void
    {
        $this->type->getAvailabilityType()->shouldBeCalled()->willReturn(Type::TYPE_MANAGEMENT_NONE);
        $this->authorizationCheckerAdapter->isGranted('ROLE_PREVIOUS_ADMIN')->shouldBeCalled()->willReturn(false);

        $service = new HasUnavailabilityManagementDisabled($this->authorizationCheckerAdapter->reveal());
        $this->assertTrue($service->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testIsNotDisabled(): void
    {
        $this->type->getAvailabilityType()->shouldBeCalled()->willReturn(Type::TYPE_MANAGEMENT_UNAVAILABLE);
        $this->authorizationCheckerAdapter->isGranted('ROLE_PREVIOUS_ADMIN')->shouldNotBeCalled();

        $service = new HasUnavailabilityManagementDisabled($this->authorizationCheckerAdapter->reveal());
        $this->assertFalse($service->isSatisfiedBy($this->sheet->reveal()));
    }

    public function testAvailabilityManagement(): void
    {
        $this->type->getAvailabilityType()->shouldBeCalled()->willReturn(Type::TYPE_MANAGEMENT_AVAILABLE);
        $this->authorizationCheckerAdapter->isGranted('ROLE_PREVIOUS_ADMIN')->shouldNotBeCalled();

        $service = new HasUnavailabilityManagementDisabled($this->authorizationCheckerAdapter->reveal());
        $this->assertTrue($service->isSatisfiedBy($this->sheet->reveal()));
    }
}
