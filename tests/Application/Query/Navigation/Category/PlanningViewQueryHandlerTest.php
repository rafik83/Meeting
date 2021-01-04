<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Category;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Category\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\PlanningViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class PlanningViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $navigationBuilder;

    /** @var ObjectProphecy */
    private $agendaAccessChecker;

    /** @var ObjectProphecy */
    private $meetingPublishedAccessChecker;

    public function setUp()
    {
        $this->navigationBuilder             = $this->prophesize(NavigationBuilderInterface::class);
        $this->agendaAccessChecker           = $this->prophesize(AgendaAccessChecker::class);
        $this->meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
    }

    public function testHandleWithoutDate()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);

        $this->navigationBuilder->getRoute(Argument::any())->shouldNotBeCalled();
        $this->agendaAccessChecker->allowedToAccess(Argument::any())->shouldNotBeCalled();
        $this->meetingPublishedAccessChecker->allowedToAccess(Argument::any())->shouldNotBeCalled();

        $handler = new PlanningViewQueryHandler(
            $this->navigationBuilder->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->meetingPublishedAccessChecker->reveal()
        );
        $result = $handler->handle(new PlanningViewQuery($sheet, $user, 'fr'));

        $linkView = new LinkView('navigation.links.incoming', null);
        $expected = new CategoryView(Category::PLANNING, Category::PLANNING_ICON, [$linkView], true);

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithHappeningDateNotPassed()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);
        $date  = new \DateTime('2016-10-12 10:00:00.000');
        $event->getConfiguration()->setDates(null, null, null, null, null, null, $date);

        $this->navigationBuilder->getRoute(Argument::any())->shouldNotBeCalled();
        $this->agendaAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $this->meetingPublishedAccessChecker->allowedToAccess(Argument::any())->shouldNotBeCalled();

        $handler = new PlanningViewQueryHandler(
            $this->navigationBuilder->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->meetingPublishedAccessChecker->reveal()
        );
        $result = $handler->handle(new PlanningViewQuery($sheet, $user, 'fr'));

        $linkView = new LinkView(
            'navigation.links.planning.available_date',
            null,
            null,
            new StateButtonView(false, '12 octobre 2016')
        );
        $expected = new CategoryView(Category::PLANNING, Category::PLANNING_ICON, [$linkView], true);

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithHappeningDatePassed()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event);
        $sheet->getId()->shouldBeCalled()->willReturn(1);

        $date  = new \DateTime('2016-10-12 10:00:00.000');
        $event->getConfiguration()->setDates(null, null, null, null, null, null, $date);

        $this->navigationBuilder->getRoute('event_agenda', ['sheet' => 1])->shouldBeCalled()->willReturn('route');
        $this->agendaAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $this->meetingPublishedAccessChecker->allowedToAccess(Argument::any())->shouldNotBeCalled();

        $handler = new PlanningViewQueryHandler(
            $this->navigationBuilder->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->meetingPublishedAccessChecker->reveal()
        );
        $result = $handler->handle(new PlanningViewQuery($sheet->reveal(), $user, 'fr'));

        $linkView = new LinkView(
            'navigation.links.planning.available_date',
            'route',
            null,
            new StateButtonView(false, '12 octobre 2016')
        );
        $expected = new CategoryView(Category::PLANNING, Category::PLANNING_ICON, [$linkView], true);

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithMeetingPublishedDateNotPassed()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);
        $date  = new \DateTime('2016-10-12 10:00:00.000');
        $event->getConfiguration()->setDates(null, null, $date);

        $this->navigationBuilder->getRoute(Argument::any())->shouldNotBeCalled();
        $this->agendaAccessChecker->allowedToAccess(Argument::any())->shouldNotBeCalled();
        $this->meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);

        $handler = new PlanningViewQueryHandler(
            $this->navigationBuilder->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->meetingPublishedAccessChecker->reveal()
        );
        $result = $handler->handle(new PlanningViewQuery($sheet, $user, 'fr'));

        $linkViewHappening = new LinkView('navigation.links.incoming', null);
        $linkView = new LinkView(
            'navigation.links.planning.final_date',
            null,
            null,
            new StateButtonView(false, '12 octobre 2016')
        );
        $expected = new CategoryView(
            Category::PLANNING,
            Category::PLANNING_ICON,
            [$linkViewHappening, $linkView],
            true
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithMeetingPublishedDatePassed()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $date  = new \DateTime('2016-10-12 10:00:00.000');
        $event->getConfiguration()->setDates(null, null, $date);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event);
        $sheet->getId()->shouldBeCalled()->willReturn(1);

        $this->navigationBuilder->getRoute('event_agenda', ['sheet' => 1])->shouldBeCalled()->willReturn('route');
        $this->agendaAccessChecker->allowedToAccess(Argument::any())->shouldNotBeCalled();
        $this->meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        $handler = new PlanningViewQueryHandler(
            $this->navigationBuilder->reveal(),
            $this->agendaAccessChecker->reveal(),
            $this->meetingPublishedAccessChecker->reveal()
        );
        $result = $handler->handle(new PlanningViewQuery($sheet->reveal(), $user, 'fr'));

        $linkViewHappening = new LinkView('navigation.links.incoming', null);
        $linkView = new LinkView(
            'navigation.links.planning.final_date',
            'route',
            null,
            new StateButtonView(false, '12 octobre 2016')
        );

        $expected = new CategoryView(
            Category::PLANNING,
            Category::PLANNING_ICON,
            [$linkViewHappening, $linkView],
            true
        );

        $this->assertEquals($expected, $result);
    }
}
