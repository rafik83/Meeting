<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Group;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Sheet\Group\Update;
use Proximum\Vimeet\Application\Command\Sheet\Group\UpdateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetGroupUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotFoundForGivenEmailException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\GroupFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandlerTest extends TestCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var UserToGroupManagerChecker */
    private $userToGroupManagerChecker;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var Group */
    private $group;

    /** @var Event */
    private $event;

    /** @var \DateTime */
    private $now;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $manager = UserFactory::create();
        $this->now = new \DateTime();
        $title = 'SheetGroup';

        $this->group = GroupFactory::createGroup($this->event, $manager, $this->now, $title);

        $this->groupRepository           = $this->prophesize(GroupRepositoryInterface::class);
        $this->eventDispatcher           = $this->prophesize(EventDispatcherInterface::class);
        $this->userRepository            = $this->prophesize(UserRepositoryInterface::class);
        $this->userToGroupManagerChecker = $this->prophesize(UserToGroupManagerChecker::class);
    }

    public function testModifyManagerEmailAndTitle()
    {
        $newEmail      = 'elao@proximum.com';
        $update        = new Update($this->group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;
        $update->forceSheetTitle = true;

        $expectedManager = UserFactory::create($newEmail);
        $expectedGroup   = GroupFactory::createGroup(
            $this->event,
            $expectedManager,
            $this->now,
            'SheetGroupNewTitle',
            true
        );

        $this->userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn($expectedManager);

        $this->userToGroupManagerChecker
            ->isUserAllowedToManageGroupOnUpdate($expectedManager, $this->group)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->groupRepository->set($expectedGroup)->shouldBeCalled();

        $this->eventDispatcher->dispatch(Events::SHEET_GROUP_UPDATED,
            new SheetGroupUpdatedEvent($expectedGroup, true)
        )->shouldBeCalled();

        $handler = new UpdateHandler(
            $this->userRepository->reveal(),
            $this->groupRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->userToGroupManagerChecker->reveal()
        );

        $handler->handle($update);
    }

    public function testManagerCantManageThisEntity()
    {
        $this->expectException(UserNotAllowedToManageGroupException::class);

        $newEmail      = 'elao@proximum.com';
        $update        = new Update($this->group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;

        $expectedManager = UserFactory::create($newEmail);

        $this->userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn($expectedManager);

        $this->userToGroupManagerChecker
            ->isUserAllowedToManageGroupOnUpdate($expectedManager, $this->group)
            ->shouldBeCalled()
            ->willThrow(UserNotAllowedToManageGroupException::class);

        $this->groupRepository->set(Argument::type(Group::class))->shouldNotBeCalled();

        $this->eventDispatcher->dispatch(Events::SHEET_GROUP_UPDATED,
            Argument::type(SheetGroupUpdatedEvent::class)
        )->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $this->userRepository->reveal(),
            $this->groupRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->userToGroupManagerChecker->reveal()
        );

        $handler->handle($update);
    }

    public function testEmailNotExist()
    {
        $this->expectException(UserNotFoundForGivenEmailException::class);

        $newEmail      = 'elao@proximum.com';
        $update        = new Update($this->group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;

        $this->userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn(null);

        $this->userToGroupManagerChecker
            ->isUserAllowedToManageGroupOnUpdate(Argument::type(User::class), Argument::type(Group::class))
            ->shouldNotBeCalled();

        $this->groupRepository->set(Argument::type(Group::class))->shouldNotBeCalled();

        $this->eventDispatcher->dispatch(Events::SHEET_GROUP_UPDATED,
            Argument::type(SheetGroupUpdatedEvent::class)
        )->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $this->userRepository->reveal(),
            $this->groupRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->userToGroupManagerChecker->reveal()
        );
        $handler->handle($update);
    }
}
