<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Service\SheetsGroup;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class GroupNameResolverTest extends TestCase
{
    /** @var Event $event */
    public $event;

    /** @var User $user */
    public $user;

    /** @var \DateTimeInterface */
    public $dateTime;

    /** @var ObjectProphecy */
    public $sheetRepository;

    /** @var GroupNameResolver */
    public $resolver;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $this->user = UserFactory::create();
        $this->dateTime = new \DateTime();

        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->resolver = new GroupNameResolver($this->sheetRepository->reveal());
    }

    public function testResolveForGroup()
    {
        $group = $this->prophesize(Sheet\Group::class);
        $group->getTitle()->shouldBeCalled()->willReturn('Proximum Group');

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getGroup()->shouldBeCalled()->willReturn($group->reveal());
        $sheet2 = $this->prophesize(Sheet::class);

        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;

        $this->assertEquals('Proximum Group', $this->resolver->resolve($this->event, $this->user));
    }

    public function testResolveForSheet()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getGroup()->shouldBeCalled()->willReturn(null);
        $sheet->getTitle()->willReturn('Proximum');
        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn([$sheet->reveal()])
        ;

        $this->assertEquals('Proximum', $this->resolver->resolve($this->event, $this->user));
    }

    public function testResolveWithNoGroupAndNoSheet()
    {
        $this->expectException(SheetNotFoundException::class);
        $this->sheetRepository->getSheetsByUserAndEvent($this->user, $this->event)->shouldBeCalled()->willReturn([]);
        $this->resolver->resolve($this->event, $this->user);
    }

    public function testResolveWithPreloadedSheets()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getGroup()->willReturn(null);
        $sheet->getTitle()->willReturn('Proximum');
        $this->sheetRepository->getSheetsByUserAndEvent($this->user, $this->event)->shouldNotBeCalled();

        $this->assertEquals('Proximum', $this->resolver->resolve($this->event, $this->user, [$sheet->reveal()]));
    }

    public function testSheetTitleIsNull()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getGroup()->willReturn(null);
        $sheet->getTitle()->willReturn(null);
        $this->sheetRepository->getSheetsByUserAndEvent($this->user, $this->event)->shouldNotBeCalled();

        $this->assertNull($this->resolver->resolve($this->event, $this->user, [$sheet->reveal()]));
    }
}
