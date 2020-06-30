<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Sheet\Group\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\AdminGroupViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\AdminGroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\GroupListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\GroupListViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Group\Admin\GroupView;
use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\AdminFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class GroupListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $admin = AdminFactory::create();

        $user = $this->prophesize(User::class);

        $reflectionGroup = new \ReflectionClass(Group::class);
        $propertyGroupId = $reflectionGroup->getProperty('id');
        $propertyGroupId->setAccessible(true);

        $group = new Group($event, $user->reveal(), 'My entity', false ,$datetime);
        $propertyGroupId->setValue($group, 1);

        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheetId = $reflectionSheet->getProperty('id');
        $propertySheetId->setAccessible(true);

        $sheet1 = SheetFactory::create($event);
        $propertySheetId->setValue($sheet1, 1);

        $sheet2 = SheetFactory::create($event);
        $propertySheetId->setValue($sheet2, 2);

        $groupViewQueryHandler = $this->prophesize(AdminGroupViewQueryHandler::class);
        $groupRepository = $this->prophesize(GroupRepositoryInterface::class);

        $expectedResult = new GroupView(
            1,
            'My entity',
            'john@email.com',
            42,
            [
                new SheetView(1, 'Sheet title 1'),
                new SheetView(2, 'Sheet title 2'),
            ],
            $datetime
        );

        $handler = new GroupListViewQueryHandler($groupViewQueryHandler->reveal(), $groupRepository->reveal());

        $groupRepository->getAllByEventOrderedByTitle($event)->shouldBeCalled()->willReturn([$group]);

        $groupViewQueryHandler
            ->handle(new AdminGroupViewQuery($group, $admin))
            ->shouldBeCalled()
            ->willReturn($expectedResult);

        $result = $handler->handle(new GroupListViewQuery($event, $admin));

        $this->assertEquals([$expectedResult], $result);
    }
}
