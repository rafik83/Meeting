<?php

namespace Proximum\Vimeet\Tests\Application\Command\Group;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Group\DuplicateToEvent;
use Proximum\Vimeet\Application\Command\Group\DuplicateToEventHandler;
use Proximum\Vimeet\Domain\Exception\Group\Duplicate\CanNotDuplicateToTheSameEventException;
use Proximum\Vimeet\Domain\Exception\Group\Duplicate\GroupAlreadyDuplicatedInGivenEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;

class DuplicateToEventHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $groupRepository;

    /** @var ObjectProphecy */
    private $dateTime;

    /** @var ObjectProphecy */
    private $group;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $userToGroupManagerChecker;

    public function setUp()
    {
        $this->userToGroupManagerChecker = $this->prophesize(UserToGroupManagerChecker::class);
        $this->groupRepository = $this->prophesize(GroupRepositoryInterface::class);
        $this->dateTime = new \DateTime();
        $this->group = $this->prophesize(Group::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testHandleSameEvent(): void
    {
        $this->expectException(CanNotDuplicateToTheSameEventException::class);

        $this->group->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->groupRepository->add(Argument::any())->shouldNotBeCalled();

        $command = new DuplicateToEvent($this->group->reveal(), $this->event->reveal());
        $handler = new DuplicateToEventHandler(
            $this->groupRepository->reveal(),
            $this->userToGroupManagerChecker->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }

    public function testHandleAlreadyDuplicated(): void
    {
        $this->expectException(GroupAlreadyDuplicatedInGivenEventException::class);

        $oldEvent = $this->prophesize(Event::class);
        $alreadyDuplicatedGroup = $this->prophesize(Group::class);

        $this->group->getEvent()->shouldBeCalled()->willReturn($oldEvent->reveal());
        $this->groupRepository->add(Argument::any())->shouldNotBeCalled();
        $this->groupRepository
            ->findDuplicatedGroupInEvent($this->group->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($alreadyDuplicatedGroup->reveal())
        ;

        $command = new DuplicateToEvent($this->group->reveal(), $this->event->reveal());
        $handler = new DuplicateToEventHandler(
            $this->groupRepository->reveal(),
            $this->userToGroupManagerChecker->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }

    public function testHandle(): void
    {
        $oldEvent = $this->prophesize(Event::class);

        $this->group->getEvent()->shouldBeCalled()->willReturn($oldEvent->reveal());
        $this->groupRepository
            ->findDuplicatedGroupInEvent($this->group->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $manager = $this->prophesize(User::class);
        $this->group->getManager()->willReturn($manager->reveal());
        $this->group->getTitle()->willReturn('title');
        $this->group->hasSheetTitleForced()->shouldBeCalled()->willReturn(false);

        $this
            ->userToGroupManagerChecker
            ->isUserToGroupManagerAllowed(
                $this->event->reveal(),
                $manager->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $expectedGroup = new Group(
            $this->event->reveal(),
            $manager->reveal(),
            'title',
            false,
            $this->dateTime,
            $this->group->reveal()
        );

        $this->groupRepository->add($expectedGroup)->shouldBeCalled();

        $command = new DuplicateToEvent($this->group->reveal(), $this->event->reveal());
        $handler = new DuplicateToEventHandler(
            $this->groupRepository->reveal(),
            $this->userToGroupManagerChecker->reveal(),
            $this->dateTime
        );

        $handler->handle($command);
    }
}
