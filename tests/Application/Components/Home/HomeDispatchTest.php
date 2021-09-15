<?php

namespace Proximum\Vimeet\Tests\Application\Components\Home;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Home\HomeDispatch;
use Proximum\Vimeet\Application\View\Home\HomeDispatchView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class HomeDispatchTest extends TestCase
{
    /** @var ObjectProphecy */
    public $sheetRepository;

    /** @var ObjectProphecy */
    public $groupRepository;

    /** @var ObjectProphecy */
    public $event;

    /** @var ObjectProphecy */
    public $user;

    public function setUp()
    {
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->groupRepository = $this->prophesize(GroupRepositoryInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
    }

    /**
     * @return HomeDispatch
     */
    private function getHomeDispatch()
    {
        return new HomeDispatch(
            $this->sheetRepository->reveal(),
            $this->groupRepository->reveal()
        );
    }

    public function testHandleGroup()
    {
        $group = $this->prophesize(Group::class);

        $this
            ->groupRepository
            ->getByEventAndManager($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($group->reveal())
        ;

        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldNotBeCalled()
        ;

        $homeDispatch = $this->getHomeDispatch();
        $homeDispatchView = $homeDispatch->handle($this->event->reveal(), $this->user->reveal());

        $expectedHomeDispatchView = new HomeDispatchView(HomeDispatchView::TYPE_GROUP, $group->reveal());

        $this->assertEquals($expectedHomeDispatchView, $homeDispatchView);
    }

    public function testHandleOneSheet()
    {
        $sheet = $this->prophesize(Sheet::class);

        $this->groupRepository
            ->getByEventAndManager($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet->reveal()])
        ;

        $homeDispatch = $this->getHomeDispatch();
        $homeDispatchView = $homeDispatch->handle($this->event->reveal(), $this->user->reveal());

        $expectedHomeDispatchView = new HomeDispatchView(HomeDispatchView::TYPE_ONE_SHEET, $sheet->reveal());

        $this->assertEquals($expectedHomeDispatchView, $homeDispatchView);
    }

    public function testHandleMultipleSheets()
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $this->groupRepository
            ->getByEventAndManager($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;

        $homeDispatch = $this->getHomeDispatch();
        $homeDispatchView = $homeDispatch->handle($this->event->reveal(), $this->user->reveal());

        $expectedHomeDispatchView = new HomeDispatchView(HomeDispatchView::TYPE_MULTIPLE_SHEETS);

        $this->assertEquals($expectedHomeDispatchView, $homeDispatchView);
    }

    public function testHandleReturnNull()
    {
        $this->groupRepository
            ->getByEventAndManager($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->sheetRepository
            ->getSheetsByUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $homeDispatch = $this->getHomeDispatch();
        $homeDispatchView = $homeDispatch->handle($this->event->reveal(), $this->user->reveal());

        $this->assertEquals(null, $homeDispatchView);
    }
}
