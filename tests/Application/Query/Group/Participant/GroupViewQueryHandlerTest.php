<?php

namespace Proximum\Vimeet\Tests\Application\Query\Group\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Group\Participant\GroupViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\GroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Group\Participant\SheetsViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\SheetsViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class GroupViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $manager = UserFactory::create();
        $sheet1  = SheetFactory::create($event, $manager);
        $day     = new Day($event, new \DateTime(), new \DateTime());

        $group = $this->prophesize(Group::class);
        $group->getId()->shouldBeCalled()->willReturn(1);
        $group->getTitle()->shouldBeCalled()->willReturn('group title');

        $sheetViews      = new SheetView(1, 'group title');
        $sheetViewsQuery = new SheetsViewQuery([$sheet1], [$day]);

        $sheetRepository        = $this->prophesize(SheetRepositoryInterface::class);
        $sheetsViewQueryHandler = $this->prophesize(SheetsViewQueryHandler::class);
        $dayRepositoryInterface = $this->prophesize(DayRepositoryInterface::class);

        $sheetRepository->getByGroup($group)->shouldBeCalled()->willReturn([$sheet1]);
        $dayRepositoryInterface->findByEvent($event)->shouldBeCalled()->willReturn([$day]);
        $sheetsViewQueryHandler->handle($sheetViewsQuery)->shouldBeCalled()->willReturn([$sheetViews]);

        $handler = new GroupViewQueryHandler(
            $sheetRepository->reveal(),
            $sheetsViewQueryHandler->reveal(),
            $dayRepositoryInterface->reveal()
        );
        $handler->handle(new GroupViewQuery($group->reveal(), $event));
    }
}
