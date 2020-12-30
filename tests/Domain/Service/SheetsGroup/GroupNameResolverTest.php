<?php

namespace Proximum\Vimeet\Tests\Domain\Service\SheetsGroup;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\User\Sheet\FirstParticipantSheetOfUserGetter;
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

    /** @var ObjectProphecy */
    public $firstParticipantSheetOfUserGetter;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $this->user = UserFactory::create();
        $this->dateTime = new \DateTime();

        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->firstParticipantSheetOfUserGetter = $this->prophesize(FirstParticipantSheetOfUserGetter::class);
        $this->resolver = new GroupNameResolver(
            $this->sheetRepository->reveal(),
            $this->firstParticipantSheetOfUserGetter->reveal()
        );
    }

    public function testResolveForGroup(): void
    {
        $group = $this->prophesize(Sheet\Group::class);
        $group->getTitle()->shouldBeCalled()->willReturn('Proximum Group');
        $group->hasSheetTitleForced()->shouldBeCalled()->willReturn(false);

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

    public function testResolveForGroupWithSheetTitleForced(): void
    {
        $group = $this->prophesize(Sheet\Group::class);
        $group->getTitle()->shouldNotBeCalled();
        $group->hasSheetTitleForced()->shouldBeCalled()->willReturn(true);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getGroup()->shouldBeCalled()->willReturn($group->reveal());
        $sheet1->getTitle()->shouldNotBeCalled();
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getGroup()->shouldBeCalled()->willReturn($group->reveal());
        $sheet2->getTitle()->shouldBeCalled()->willReturn('Another title');

        $sheets = [$sheet1->reveal(), $sheet2->reveal()];

        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;


        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $this->firstParticipantSheetOfUserGetter
            ->getFirstParticipantSheet($this->user, $sheets)
            ->shouldBeCalled()
            ->willReturn($sheet2->reveal())
        ;

        $this->assertEquals('Another title', $this->resolver->resolve($this->event, $this->user));
    }

    public function testResolveForMultipleGroupWithSheetTitleForcedAndNot(): void
    {
        $group1 = $this->prophesize(Sheet\Group::class);
        $group1->getTitle()->shouldNotBeCalled();
        $group1->hasSheetTitleForced()->shouldBeCalled()->willReturn(true);
        $group2 = $this->prophesize(Sheet\Group::class);
        $group2->getTitle()->shouldBeCalled()->willReturn('Proximum Inc.');
        $group2->hasSheetTitleForced()->shouldBeCalled()->willReturn(false);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getGroup()->shouldBeCalled()->willReturn($group1->reveal());
        $sheet1->getTitle()->shouldNotBeCalled();
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getGroup()->shouldBeCalled()->willReturn($group2->reveal());
        $sheet2->getTitle()->shouldNotBeCalled();

        $sheets = [$sheet1->reveal(), $sheet2->reveal()];
        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $this->firstParticipantSheetOfUserGetter->getFirstParticipantSheet($this->user, $sheets)->shouldNotBeCalled();

        $this->assertEquals('Proximum Inc.', $this->resolver->resolve($this->event, $this->user));
    }

    public function testResolveForSheet(): void
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getGroup()->shouldBeCalled()->willReturn(null);
        $sheet->getTitle()->willReturn('Proximum');
        $sheets = [$sheet->reveal()];
        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $this->firstParticipantSheetOfUserGetter
            ->getFirstParticipantSheet($this->user, $sheets)
            ->shouldBeCalled()
            ->willReturn($sheet->reveal())
        ;

        $this->assertEquals('Proximum', $this->resolver->resolve($this->event, $this->user));
    }

    public function testResolveWithNoGroupAndNoSheet(): void
    {
        $this->expectException(SheetNotFoundException::class);
        $this->sheetRepository->getSheetsByUserAndEvent($this->user, $this->event)->shouldBeCalled()->willReturn([]);
        $this->resolver->resolve($this->event, $this->user);
    }

    public function testResolveWithPreloadedSheets(): void
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getGroup()->willReturn(null);
        $sheet->getTitle()->willReturn('Proximum');
        $this->sheetRepository->getSheetsByUserAndEvent($this->user, $this->event)->shouldNotBeCalled();
        $sheets = [$sheet->reveal()];

        $this->firstParticipantSheetOfUserGetter
            ->getFirstParticipantSheet($this->user, $sheets)
            ->shouldBeCalled()
            ->willReturn($sheet->reveal())
        ;

        $this->assertEquals('Proximum', $this->resolver->resolve($this->event, $this->user, [$sheet->reveal()]));
    }

    public function testSheetTitleIsNull(): void
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getGroup()->willReturn(null);
        $sheet->getTitle()->willReturn(null);
        $sheets = [$sheet->reveal()];
        $this->firstParticipantSheetOfUserGetter
            ->getFirstParticipantSheet($this->user, $sheets)
            ->shouldBeCalled()
            ->willReturn($sheet->reveal())
        ;
        $this->sheetRepository->getSheetsByUserAndEvent($this->user, $this->event)->shouldNotBeCalled();

        $this->assertNull($this->resolver->resolve($this->event, $this->user, [$sheet->reveal()]));
    }
}
