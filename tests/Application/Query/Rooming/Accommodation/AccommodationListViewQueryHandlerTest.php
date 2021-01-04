<?php


namespace Proximum\Vimeet\Application\Query\Rooming\Accommodation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Rooming\Accommodation\AccommodationListViewQueryHandler;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\AccommodationListView;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\AccommodationView;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\OvernightCapacityView;
use Proximum\Vimeet\Application\View\Rooming\Accommodation\OvernightTotalView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\AccommodationOvernightCapacity;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\View\Rooming\AccommodationStayView;

class AccommodationListViewQueryHandlerTest extends TestCase
{
    private $accommodationRepository;
    private $stayRepository;
    private $handler;

    public function setUp()
    {
        $this->accommodationRepository = $this->prophesize(AccommodationRepositoryInterface::class);
        $this->stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $this->handler = new AccommodationListViewQueryHandler(
            $this->accommodationRepository->reveal(),
            $this->stayRepository->reveal()
        );
    }

    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $accommodation1 = $this->prophesize(Accommodation::class);
        $accommodation2 = $this->prophesize(Accommodation::class);
        $accommodation1->getId()->willReturn(1);
        $accommodation2->getId()->willReturn(2);
        $accommodation1->getTitle()->willReturn('Lime');
        $accommodation2->getTitle()->willReturn('Apricot');

        $arrival1 = new \DateTime('2018-12-31 08:30:00.000');
        $departure1 = new \DateTime('2019-01-01 08:30:00.000');
        $departure2 = new \DateTime('2019-01-02 08:30:00.000');

        $accommodationStayView1 = new AccommodationStayView(1, $arrival1, $departure1, 1);
        $accommodationStayView2 = new AccommodationStayView(2, $arrival1, $departure2, 2);

        $this->accommodationRepository
            ->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$accommodation1->reveal(), $accommodation2->reveal()])
        ;

        $this->stayRepository
            ->getAccommodationStaysByEvent($event)
            ->shouldBeCalled()
            ->willReturn([$accommodationStayView1, $accommodationStayView2])
        ;

        $accommodationView1 = new AccommodationView(1, 'Lime');
        $accommodationView2 = new AccommodationView(2, 'Apricot');

        $accommodation1OvernightCapacityThirtyFirst = $this->prophesize(AccommodationOvernightCapacity::class);
        $accommodation1OvernightCapacityThirtyFirst->getCapacity()->willReturn(10);
        $accommodation1OvernightCapacityThirtyFirst->getDate()->willReturn(new \DateTime('2018-12-31 00:00:00.000'));
        $accommodation2OvernightCapacityThirtyFirst = $this->prophesize(AccommodationOvernightCapacity::class);
        $accommodation2OvernightCapacityThirtyFirst->getCapacity()->willReturn(5);
        $accommodation2OvernightCapacityThirtyFirst->getDate()->willReturn(new \DateTime('2018-12-31 00:00:00.000'));
        $accommodation1OvernightCapacityFirst = $this->prophesize(AccommodationOvernightCapacity::class);
        $accommodation1OvernightCapacityFirst->getCapacity()->willReturn(12);
        $accommodation1OvernightCapacityFirst->getDate()->willReturn(new \DateTime('2019-01-01 00:00:00.000'));
        $accommodation2OvernightCapacityFirst = $this->prophesize(AccommodationOvernightCapacity::class);
        $accommodation2OvernightCapacityFirst->getCapacity()->willReturn(7);
        $accommodation2OvernightCapacityFirst->getDate()->willReturn(new \DateTime('2019-01-01 00:00:00.000'));
        $accommodation2OvernightCapacitySecond = $this->prophesize(AccommodationOvernightCapacity::class);
        $accommodation2OvernightCapacitySecond->getCapacity()->willReturn(18);
        $accommodation2OvernightCapacitySecond->getDate()->willReturn(new \DateTime('2019-01-02 00:00:00.000'));

        $accommodation1->getOvernightCapacities()->willReturn([
            $accommodation1OvernightCapacityThirtyFirst->reveal(),
            $accommodation1OvernightCapacityFirst->reveal(),
        ]);
        $accommodation2->getOvernightCapacities()->willReturn([
            $accommodation2OvernightCapacityThirtyFirst->reveal(),
            $accommodation2OvernightCapacityFirst->reveal(),
            $accommodation2OvernightCapacitySecond->reveal(),
        ]);

        $overnightTotalViewThirtyFirst = new OvernightTotalView('31/12/2018', new \DateTime('2018-12-31 00:00:00.000'), 15);
        $overnightTotalViewThirtyFirst->remaining = 13;
        $overnightTotalViewFirst = new OvernightTotalView('01/01/2019', new \DateTime('2019-01-01 00:00:00.000'), 19);
        $overnightTotalViewFirst->remaining = 18;
        $overnightTotalViewSecond = new OvernightTotalView('02/01/2019', new \DateTime('2019-01-02 00:00:00.000'), 18);

        $overnightCapacityView1 = new OvernightCapacityView(new \DateTime('2018-12-31 00:00:00.000'), 10);
        $overnightCapacityView1->remaining = 9;
        $overnightCapacityView2 = new OvernightCapacityView(new \DateTime('2019-01-01 00:00:00.000'), 12);
        $overnightCapacityView3 = new OvernightCapacityView(new \DateTime('2018-12-31 00:00:00.000'), 5);
        $overnightCapacityView3->remaining = 4;
        $overnightCapacityView4 = new OvernightCapacityView(new \DateTime('2019-01-01 00:00:00.000'), 7);
        $overnightCapacityView4->remaining = 6;
        $overnightCapacityView5 = new OvernightCapacityView(new \DateTime('2019-01-02 00:00:00.000'), 18);

        $accommodationView1->addOvernightCapacityView(
            '31/12/2018',
            $overnightCapacityView1
        );
        $accommodationView1->addOvernightCapacityView(
            '01/01/2019',
            $overnightCapacityView2
        );
        $accommodationView2->addOvernightCapacityView(
            '31/12/2018',
            $overnightCapacityView3
        );
        $accommodationView2->addOvernightCapacityView(
            '01/01/2019',
            $overnightCapacityView4
        );
        $accommodationView2->addOvernightCapacityView(
            '02/01/2019',
            $overnightCapacityView5
        );

        $accommodationViews = [
            2 => $accommodationView2,
            1 => $accommodationView1,
        ];

        $overnightTotalViews = [
            '31/12/2018' => $overnightTotalViewThirtyFirst,
            '01/01/2019' => $overnightTotalViewFirst,
            '02/01/2019' => $overnightTotalViewSecond,
        ];

        $query = new AccommodationListViewQuery($event->reveal());

        $result = $this->handler->handle($query);

        $expected = new AccommodationListView($accommodationViews, $overnightTotalViews);

        $this->assertEquals($expected, $result);
    }
}
