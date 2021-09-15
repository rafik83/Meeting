<?php

namespace Proximum\Vimeet\Tests\Application\Components\Group;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Group\DuplicateToEvent;
use Proximum\Vimeet\Application\Command\Group\DuplicateToEventHandler;
use Proximum\Vimeet\Application\Components\Group\GroupDuplicator;
use Proximum\Vimeet\Domain\Exception\Group\Duplicate\CanNotDuplicateToTheSameEventException;
use Proximum\Vimeet\Domain\Exception\Group\Duplicate\GroupAlreadyDuplicatedInGivenEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class GroupDuplicatorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $group;

    /** @var ObjectProphecy */
    private $duplicateToEventHandler;

    public function setUp()
    {
        $this->group = $this->prophesize(Group::class);
        $this->event = $this->prophesize(Event::class);
        $this->duplicateToEventHandler = $this->prophesize(DuplicateToEventHandler::class);
    }

    public function testDuplicateToEventWithSameEvent(): void
    {
        $this->expectException(CanNotDuplicateToTheSameEventException::class);

        $exception = new CanNotDuplicateToTheSameEventException();
        $this->duplicateToEventHandler->handle(new DuplicateToEvent($this->group->reveal(), $this->event->reveal()))
            ->shouldBeCalled()
            ->willThrow($exception)
        ;

        $groupDuplicator = new GroupDuplicator(
            $this->duplicateToEventHandler->reveal()
        );

        $groupDuplicator->duplicateToEvent($this->group->reveal(), $this->event->reveal());
    }

    public function testDuplicateToEventWithAlreadyDuplicatedGroup(): void
    {
        $duplicatedGroup = $this->prophesize(Group::class);
        $exception = new GroupAlreadyDuplicatedInGivenEventException($duplicatedGroup->reveal());
        $this->duplicateToEventHandler->handle(new DuplicateToEvent($this->group->reveal(), $this->event->reveal()))
            ->shouldBeCalled()
            ->willThrow($exception)
        ;

        $groupDuplicator = new GroupDuplicator(
            $this->duplicateToEventHandler->reveal()
        );

        $result = $groupDuplicator->duplicateToEvent($this->group->reveal(), $this->event->reveal());

        $this->assertEquals($duplicatedGroup->reveal(), $result);
    }

    public function testDuplicateToEvent(): void
    {
        $duplicatedGroup = $this->prophesize(Group::class);
        $this->duplicateToEventHandler->handle(new DuplicateToEvent($this->group->reveal(), $this->event->reveal()))
            ->shouldBeCalled()
            ->willReturn($duplicatedGroup)
        ;

        $groupDuplicator = new GroupDuplicator(
            $this->duplicateToEventHandler->reveal()
        );

        $result = $groupDuplicator->duplicateToEvent($this->group->reveal(), $this->event->reveal());

        $this->assertEquals($duplicatedGroup->reveal(), $result);
    }
}
