<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Service\SheetsGroup;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\GroupFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class GroupNameResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveForGroup()
    {
        $now        = new \DateTime();
        $event      = EventFactory::createEvent();
        $user       = UserFactory::create();
        $groupTitle = 'ProximumGroup';
        $group      = GroupFactory::createGroup($event, $user, $now, $groupTitle);

        $groupRepository = $this->prophesize(GroupRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $groupRepository->getByUserAndEvent($user, $event)->shouldBeCalled()->willReturn($group);

        $resolver = new GroupNameResolver($groupRepository->reveal(), $sheetRepository->reveal());
        $name     = $resolver->resolve($event, $user);

        $this->assertEquals($groupTitle, $name);
    }

    public function testResolveForSheet()
    {
        $event      = EventFactory::createEvent();
        $user       = UserFactory::create();
        $sheet      = $this->prophesize(Sheet::class);
        $sheetTitle = 'Proximum';
        $sheet->getTitle()->willReturn($sheetTitle);

        $groupRepository = $this->prophesize(GroupRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $groupRepository->getByUserAndEvent($user, $event)->shouldBeCalled()->willReturn(null);

        $sheetRepository->getSheetsByUserAndEvent($user, $event)->shouldBeCalled()->willReturn([$sheet]);

        $resolver = new GroupNameResolver($groupRepository->reveal(), $sheetRepository->reveal());
        $name     = $resolver->resolve($event, $user);

        $this->assertEquals($sheetTitle, $name);
    }

    public function testResolveWithNoGroupAndNoSheet()
    {
        $this->expectException(SheetNotFoundException::class);

        $event      = EventFactory::createEvent();
        $user       = UserFactory::create();
        $sheet      = $this->prophesize(Sheet::class);
        $sheetTitle = 'Proximum';
        $sheet->getTitle()->willReturn($sheetTitle);

        $groupRepository = $this->prophesize(GroupRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $groupRepository->getByUserAndEvent($user, $event)->shouldBeCalled()->willReturn(null);

        $sheetRepository->getSheetsByUserAndEvent($user, $event)->shouldBeCalled()->willReturn([]);

        $resolver = new GroupNameResolver($groupRepository->reveal(), $sheetRepository->reveal());
        $resolver->resolve($event, $user);
    }
}
