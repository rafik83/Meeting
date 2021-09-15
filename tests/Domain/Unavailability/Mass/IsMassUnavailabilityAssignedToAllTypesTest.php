<?php

namespace Proximum\Vimeet\Tests\Domain\Unavailability\Mass;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Mass\IsMassUnavailabilityAssignedToAllTypes;

class IsMassUnavailabilityAssignedToAllTypesTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(1337);

        $mass1 = $this->prophesize(Mass::class);
        $mass1->countTypes()->shouldBeCalled()->willReturn(3);

        $mass2 = $this->prophesize(Mass::class);
        $mass2->countTypes()->shouldBeCalled()->willReturn(4);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->countByEvent($event->reveal())->shouldBeCalledTimes(1)->willReturn(4);

        $isMassUnavailabilityAssignedToAllTypes = new IsMassUnavailabilityAssignedToAllTypes($typeRepository->reveal());
        $this->assertFalse($isMassUnavailabilityAssignedToAllTypes->handle($event->reveal(), $mass1->reveal()));
        $this->assertTrue($isMassUnavailabilityAssignedToAllTypes->handle($event->reveal(), $mass2->reveal()));
    }
}
