<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Happening\DayViewQuery;
use Proximum\Vimeet\Application\Query\Happening\DayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Happening\HappeningViewQuery;
use Proximum\Vimeet\Application\Query\Happening\HappeningViewQueryHandler;
use Proximum\Vimeet\Application\Query\Happening\MassUnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Happening\MassUnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Happening\DayView;
use Proximum\Vimeet\Application\View\Happening\HappeningCategoryView;
use Proximum\Vimeet\Application\View\Happening\HappeningView;
use Proximum\Vimeet\Application\View\Happening\MassUnavailabilityView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class DayViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user = UserFactory::create();

        $event     = EventFactory::createEvent();
        $sheet     = $this->prophesize(Sheet::class);
        $type      = $this->prophesize(Type::class);
        $category  = null;
        $massStartTime = new \DateTime('2016-10-12 11:00:00');
        $massEndTime   = new \DateTime('2016-10-12 19:00:00');
        $timeRangeStartTime = new \DateTime('2016-10-12 10:00:00');
        $timeRangeEndTime   = new \DateTime('2016-10-12 18:00:00');
        $timeRange = new TimeRangeView($timeRangeStartTime, $timeRangeEndTime);
        $locale = 'fr';

        $sheet->getType()->willReturn($type->reveal());

        // Mass
        $categoryMass = new Unavailability\Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        $mass = new Unavailability\Mass($event, $categoryMass, 'name', $massStartTime, $massEndTime, true);

        // Data
        $beginHappening1 = new \DateTime('2016-10-12 12:00:00');
        $beginHappening2 = new \DateTime('2016-10-12 15:30:00');
        $endHappening1   = new \DateTime('2016-10-12 14:00:00');
        $endHappening2   = new \DateTime('2016-10-12 16:50:00');
        $categoryH1      = new Happening\Category($event, 'Conference', 1, '#123123', '#123123');
        $categoryH2      = new Happening\Category($event, 'RDV', 2, '#123123', '#123123');
        $happening1 = new Happening(
            $event,
            $beginHappening1,
            $endHappening1,
            $categoryH1,
            []
        );

        $happening2 = new Happening(
            $event,
            $beginHappening2,
            $endHappening2,
            $categoryH2,
            []
        );

        $reflection = new \ReflectionClass(Happening::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($happening1, 1);
        $property->setValue($happening2, 2);
        $property->setAccessible(false);

        $happenings = [
            $happening1,
            $happening2,
        ];

        // Expected
        $happeningCategoryView = new HappeningCategoryView('title', 'Conference', '#123123', '#123123');
        $happeningView1 = new HappeningView(
            1,
            $happeningCategoryView,
            $beginHappening1,
            $endHappening1,
            'title',
            'description',
            null,
            [],
            'Europe/Paris'
        );
        $happeningView2 = new HappeningView(
            2,
            $happeningCategoryView,
            $beginHappening2,
            $endHappening2,
            'title2',
            'description2',
            null,
            [],
            'Europe/Paris'
        );

        $massView = new MassUnavailabilityView(
            1,
            $massStartTime,
            $massEndTime,
            'title',
            'description',
            'picto',
            'leftColor',
            'rightColor',
            'Europe/Paris',
            false
        );

        $expected = new DayView(
            $timeRangeStartTime,
            $timeRangeEndTime,
            $event->getConfiguration()->getScheduleScale(),
            [
                $happeningView1,
                $happeningView2,
            ],
            [
                $massView,
                $happeningView1,
                $happeningView2,
            ]
        );

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->findByEventAndTypeAndDayAndCategory(
            $event,
            $type->reveal(),
            $timeRange->getBegin(),
            $locale,
            $category
        )->shouldBeCalled()->willReturn($happenings);

        $happeningViewQueryHandler = $this->prophesize(HappeningViewQueryHandler::class);
        $happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $user,
                $happening1,
                $event,
                'fr'
            )
        )->shouldBeCalled()->willReturn($happeningView1);
        $happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $user,
                $happening2,
                $event,
                'fr'
            )
        )->shouldBeCalled()->willReturn($happeningView2);

        $massHandler = $this->prophesize(MassUnavailabilityViewQueryHandler::class);
        $massHandler
            ->handle(new MassUnavailabilityViewQuery($mass, $event, 'fr'))
            ->shouldBeCalled()
            ->willReturn($massView);

        $handler = new DayViewQueryHandler(
            $happeningRepository->reveal(),
            $happeningViewQueryHandler->reveal(),
            $massHandler->reveal()
        );
        $result = $handler->handle(new DayViewQuery(
            $event,
            $sheet->reveal(),
            $user,
            $timeRange,
            'fr',
            $category,
            [$mass]
        ));

        $this->assertEquals($expected, $result);
    }
}
