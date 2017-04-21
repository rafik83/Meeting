<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Sheet\Group\Admin;


use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\GroupViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\GroupViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Group\Admin\GroupView;
use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class GroupViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $user = UserFactory::create();

        $reflectionGroup = new \ReflectionClass(Group::class);
        $propertyGroupId = $reflectionGroup->getProperty('id');
        $propertyGroupId->setAccessible(true);

        $group = new Group($event, $user, 'My entity', $datetime);
        $propertyGroupId->setValue($group, 1);

        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheetId = $reflectionSheet->getProperty('id');
        $propertySheetId->setAccessible(true);

        $sheet1 = SheetFactory::create($event);
        $propertySheetId->setValue($sheet1, 1);

        $sheet2 = SheetFactory::create($event);
        $propertySheetId->setValue($sheet2, 2);

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $expectedGroupView = new GroupView(
            1,
            'My entity',
            $user->getEmail(),
            [
                new SheetView(1, 'Sheet title 1'),
                new SheetView(2, 'Sheet title 2'),
            ],
            $datetime
        );

        $sheetRepository->getByGroup($group)->shouldBeCalled()->willReturn([$sheet1, $sheet2]);

        $sheetInfoGuesser->guessSheetTitle($sheet1)->shouldBeCalled()->willReturn('Sheet title 1');
        $sheetInfoGuesser->guessSheetTitle($sheet2)->shouldBeCalled()->willReturn('Sheet title 2');

        $handler = new GroupViewQueryHandler($sheetRepository->reveal(), $sheetInfoGuesser->reveal());
        $groupView = $handler->handle(new GroupViewQuery($group));

        $this->assertEquals($expectedGroupView, $groupView);
    }
}
