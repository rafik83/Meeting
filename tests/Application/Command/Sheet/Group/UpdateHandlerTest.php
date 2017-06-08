<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Group;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Sheet\Group\Update;
use Proximum\Vimeet\Application\Command\Sheet\Group\UpdateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetGroupCreatedEvent;
use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotFoundForGivenEmailException;
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
    public function testModifyManagerEmailAndTitle()
    {
        $event   = EventFactory::createEvent();
        $manager = UserFactory::create();
        $now     = new \DateTime();
        $title   = 'SheetGroup';

        $group = GroupFactory::createGroup($event, $manager, $now, $title);

        $newEmail      = 'elao@proximum.com';
        $update        = new Update($group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;

        $expectedManager = UserFactory::create($newEmail);
        $expectedGroup   = GroupFactory::createGroup($event, $expectedManager, $now);

        $groupRepository           = $this->prophesize(GroupRepositoryInterface::class);
        $eventDispatcher           = $this->prophesize(EventDispatcherInterface::class);
        $userRepository            = $this->prophesize(UserRepositoryInterface::class);
        $userToGroupManagerChecker = $this->prophesize(UserToGroupManagerChecker::class);

        $userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn($expectedManager);

        $userToGroupManagerChecker->isUserToGroupManagerAllowed($event, $expectedManager)
            ->shouldBeCalled();

        $groupRepository->update($expectedGroup)->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_GROUP_CREATED,
            new SheetGroupCreatedEvent($group)
        )->shouldBeCalled();

        $handler = new UpdateHandler(
            $userRepository->reveal(),
            $groupRepository->reveal(),
            $eventDispatcher->reveal(),
            $userToGroupManagerChecker->reveal()
        );

        $handler->handle($update);
    }

    public function testManagerCantManageThisEntity()
    {
        $this->expectException(UserNotAllowedToManageGroupException::class);

        $event   = EventFactory::createEvent();
        $manager = UserFactory::create();
        $now     = new \DateTime();
        $title   = 'SheetGroup';

        $group = GroupFactory::createGroup($event, $manager, $now, $title);

        $newEmail      = 'elao@proximum.com';
        $update        = new Update($group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;

        $expectedManager = UserFactory::create($newEmail);

        $groupRepository           = $this->prophesize(GroupRepositoryInterface::class);
        $eventDispatcher           = $this->prophesize(EventDispatcherInterface::class);
        $userRepository            = $this->prophesize(UserRepositoryInterface::class);
        $userToGroupManagerChecker = $this->prophesize(UserToGroupManagerChecker::class);

        $userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn($expectedManager);

        $userToGroupManagerChecker->isUserToGroupManagerAllowed($event, $expectedManager)
            ->shouldBeCalled();

        $groupRepository->update(Argument::type(Group::class))->shouldNotBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_GROUP_CREATED,
            Argument::type(SheetGroupCreatedEvent::class)
        )->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $userRepository->reveal(),
            $groupRepository->reveal(),
            $eventDispatcher->reveal(),
            $userToGroupManagerChecker->reveal()
        );

        $handler->handle($update);
    }

    public function testEmailNotExist()
    {
        $this->expectException(UserNotFoundForGivenEmailException::class);

        $event   = EventFactory::createEvent();
        $manager = UserFactory::create();
        $now     = new \DateTime();
        $title   = 'SheetGroup';

        $group = GroupFactory::createGroup($event, $manager, $now, $title);

        $newEmail      = 'elao@proximum.com';
        $update        = new Update($group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;

        $groupRepository           = $this->prophesize(GroupRepositoryInterface::class);
        $eventDispatcher           = $this->prophesize(EventDispatcherInterface::class);
        $userRepository            = $this->prophesize(UserRepositoryInterface::class);
        $userToGroupManagerChecker = $this->prophesize(UserToGroupManagerChecker::class);

        $userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn(null);

        $userToGroupManagerChecker->isUserToGroupManagerAllowed($event, Argument::type(User::class))
            ->shouldNotBeCalled();

        $groupRepository->update(Argument::type(Group::class))->shouldNotBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_GROUP_CREATED,
            Argument::type(SheetGroupCreatedEvent::class)
        )->shouldNotBeCalled();

        $handler = new UpdateHandler($userRepository->reveal(), $groupRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($update);
    }
}
