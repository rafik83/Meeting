<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Sheet\Group\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ImpersonateUrlGeneratorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\AdminGroupViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\AdminGroupViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Group\Admin\GroupView;
use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\AdminFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class GroupViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();
        $admin    = AdminFactory::create();
        $event    = EventFactory::createEvent();
        $user     = UserFactory::create();

        $reflectionGroup = new \ReflectionClass(Group::class);
        $propertyGroupId = $reflectionGroup->getProperty('id');
        $propertyGroupId->setAccessible(true);

        $group = new Group($event, $user, 'My entity', $datetime);
        $propertyGroupId->setValue($group, 1);

        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheetId = $reflectionSheet->getProperty('id');
        $propertySheetId->setAccessible(true);

        $sheet1 = SheetFactory::create($event);
        $sheet1->setTitle('Sheet title 1');
        $propertySheetId->setValue($sheet1, 1);

        $sheet2 = SheetFactory::create($event);
        $sheet2->setTitle('Sheet title 2');
        $propertySheetId->setValue($sheet2, 2);

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $impersonateUrlGenerator = $this->prophesize(ImpersonateUrlGeneratorInterface::class);

        $expectedGroupView = new GroupView(
            1,
            'My entity',
            $user->getEmail(),
            [
                new SheetView(1, 'Sheet title 1'),
                new SheetView(2, 'Sheet title 2'),
            ],
            '_IMPERSONATE_LINK_',
            $datetime
        );

        $sheetRepository->getByGroup($group)->shouldBeCalled()->willReturn([$sheet1, $sheet2]);

        $impersonateUrlGenerator->generate(
            $admin,
            $user,
            $event,
            'event_sheet_group_index',
            ['sheetGroup' => 1]
        )->shouldBeCalled()->willReturn('_IMPERSONATE_LINK_');

        $handler = new AdminGroupViewQueryHandler($sheetRepository->reveal(), $impersonateUrlGenerator->reveal());

        $groupView = $handler->handle(new AdminGroupViewQuery($group, $admin));

        $this->assertEquals($expectedGroupView, $groupView);
    }
}
