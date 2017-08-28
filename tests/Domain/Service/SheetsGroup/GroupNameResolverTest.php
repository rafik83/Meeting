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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\GroupFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

class GroupNameResolverTest extends TestCase
{
    /** @var Event $event */
    public $event;
    
    /** @var User $user */
    public $user;
    
    /** @var \DateTimeInterface */
    public $dateTime;

    /** @var GroupRepositoryInterface */
    public $groupRepository;

    /** @var SheetRepositoryInterface */
    public $sheetRepository;

    /** @var GroupNameResolver */
    public $resolver;
    
    
    public function setUp()
    {
        $this->event    = EventFactory::createEvent();
        $this->user     = UserFactory::create();
        $this->dateTime = new \DateTime();

        $this->groupRepository = $this->prophesize(GroupRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->resolver        = new GroupNameResolver(
            $this->groupRepository->reveal(),
            $this->sheetRepository->reveal()
        );
    }
    
    public function testResolveForGroup()
    {
        $event      = $this->event;
        $user       = $this->user;
        $groupTitle = 'ProximumGroup';
        $group      = GroupFactory::createGroup($event, $user, $this->dateTime, $groupTitle);

        $this->groupRepository->getByUserAndEvent($user, $event)->shouldBeCalled()->willReturn($group);

        $this->assertEquals($groupTitle, $this->resolver->resolve($event, $user));
    }

    public function testResolveForSheet()
    {
        $user       = $this->user;
        $event      = $this->event;
        $sheet      = $this->prophesize(Sheet::class);
        $sheetTitle = 'Proximum';
        $sheet->getTitle()->willReturn($sheetTitle);

        $this->groupRepository->getByUserAndEvent($user, $event)->shouldBeCalled()->willReturn(null);

        $this->sheetRepository->getSheetsByUserAndEvent($user, $event)->shouldBeCalled()->willReturn([$sheet]);

        $this->assertEquals($sheetTitle, $this->resolver->resolve($event, $user));
    }

    public function testResolveWithNoGroupAndNoSheet()
    {
        $this->expectException(SheetNotFoundException::class);

        $event      = $this->event;
        $user       = $this->user;
        $sheet      = $this->prophesize(Sheet::class);
        $sheetTitle = 'Proximum';
        $sheet->getTitle()->willReturn($sheetTitle);

        $this->groupRepository->getByUserAndEvent($user, $event)->shouldBeCalled()->willReturn(null);

        $this->sheetRepository->getSheetsByUserAndEvent($user, $event)->shouldBeCalled()->willReturn([]);

        $this->resolver->resolve($event, $user);
    }
    
    public function testResolveWithPreloadedSheets()
    {
        $sheet      = $this->prophesize(Sheet::class);
        $sheetTitle = 'Proximum';
        $sheet->getTitle()->willReturn($sheetTitle);
        $sheets = [$sheet->reveal()];

        $this->groupRepository->getByUserAndEvent($this->user, $this->event)->shouldBeCalled()->willReturn(null);

        $this->sheetRepository->getSheetsByUserAndEvent($this->user, $this->event)->shouldNotBeCalled();

        $this->assertEquals($sheetTitle, $this->resolver->resolve($this->event, $this->user, $sheets));
    }
}
