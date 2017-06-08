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

    public function setUp()
    {
        $event   = EventFactory::createEvent();
        $manager = UserFactory::create();
        $now     = new \DateTime();
        $title   = 'SheetGroup';

        $this->group = GroupFactory::createGroup($event, $manager, $now, $title);

        $this->groupRepository           = $this->prophesize(GroupRepositoryInterface::class);
        $this->eventDispatcher           = $this->prophesize(EventDispatcherInterface::class);
        $this->userRepository            = $this->prophesize(UserRepositoryInterface::class);
        $this->userToGroupManagerChecker = $this->prophesize(UserToGroupManagerChecker::class);
    }

    public function testModifyManagerEmailAndTitle()
    {
        $event = EventFactory::createEvent();
        $now   = new \DateTime();

        $newEmail      = 'elao@proximum.com';
        $update        = new Update($this->group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;

        $expectedManager = UserFactory::create($newEmail);
        $expectedGroup   = GroupFactory::createGroup($event, $expectedManager, $now);

        $this->userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn($expectedManager);

        $this->userToGroupManagerChecker->isUserToGroupManagerAllowed($event, $expectedManager)
            ->shouldBeCalled();

        $this->groupRepository->update($expectedGroup)->shouldBeCalled();

        $this->eventDispatcher->dispatch(Events::SHEET_GROUP_CREATED,
            new SheetGroupCreatedEvent($expectedGroup)
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

        $event = EventFactory::createEvent();

        $newEmail      = 'elao@proximum.com';
        $update        = new Update($this->group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;

        $expectedManager = UserFactory::create($newEmail);

        $this->userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn($expectedManager);

        $this->userToGroupManagerChecker->isUserToGroupManagerAllowed($event, $expectedManager)
            ->shouldBeCalled()
            ->willThrow(\Exception::class);

        $this->groupRepository->update(Argument::type(Group::class))->shouldNotBeCalled();

        $this->eventDispatcher->dispatch(Events::SHEET_GROUP_CREATED,
            Argument::type(SheetGroupCreatedEvent::class)
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

        $event   = EventFactory::createEvent();
        $manager = UserFactory::create();
        $now     = new \DateTime();
        $title   = 'SheetGroup';

        $group = GroupFactory::createGroup($event, $manager, $now, $title);

        $newEmail      = 'elao@proximum.com';
        $update        = new Update($group);
        $update->title = 'SheetGroupNewTitle';
        $update->email = $newEmail;

        $this->userRepository->findByEmail($newEmail)->shouldBeCalled()->willReturn(null);

        $this->userToGroupManagerChecker
            ->isUserToGroupManagerAllowed($event, Argument::type(User::class))
            ->shouldNotBeCalled();

        $this->groupRepository->update(Argument::type(Group::class))->shouldNotBeCalled();

        $this->eventDispatcher->dispatch(Events::SHEET_GROUP_CREATED,
            Argument::type(SheetGroupCreatedEvent::class)
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
