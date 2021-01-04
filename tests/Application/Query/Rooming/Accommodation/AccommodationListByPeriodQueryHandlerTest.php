<?php

namespace Proximum\Vimeet\Tests\Application\Query\Rooming\Accommodation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Rooming\Accommodation\AccommodationListByPeriodQuery;
use Proximum\Vimeet\Application\Query\Rooming\Accommodation\AccommodationListByPeriodQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasRemainingOvernight;

class AccommodationListByPeriodQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $arrival = new \DateTime('2018-10-12');
        $departure = new \DateTime('2018-10-16');

        $accommodationRepository = $this->prophesize(AccommodationRepositoryInterface::class);
        $hasRemainingOvernight = $this->prophesize(HasRemainingOvernight::class);

        $accommodation1 = $this->prophesize(Accommodation::class);
        $accommodation2 = $this->prophesize(Accommodation::class);
        $accommodation3 = $this->prophesize(Accommodation::class);
        $accommodations = [
            $accommodation1->reveal(),
            $accommodation2->reveal(),
            $accommodation3->reveal(),
        ];
        $accommodationRepository->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn($accommodations)
        ;

        $hasRemainingOvernight
            ->isSatisfiedBy($accommodation1->reveal(), $arrival, $departure)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $hasRemainingOvernight
            ->isSatisfiedBy($accommodation2->reveal(), $arrival, $departure)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $hasRemainingOvernight
            ->isSatisfiedBy($accommodation3->reveal(), $arrival, $departure)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $handler = new AccommodationListByPeriodQueryHandler(
            $accommodationRepository->reveal(),
            $hasRemainingOvernight->reveal()
        );

        $result = $handler->handle(new AccommodationListByPeriodQuery(
            $event->reveal(),
            $arrival,
            $departure
        ));

        $expected = [
            $accommodation1->reveal(),
            $accommodation3->reveal(),
        ];

        $this->assertEquals($expected, $result);
    }
}
