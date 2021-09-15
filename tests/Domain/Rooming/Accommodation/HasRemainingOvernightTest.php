<?php

namespace Proximum\Vimeet\Tests\Domain\Rooming\Accommodation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\AccommodationOvernightCapacity;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasRemainingOvernight;
use Proximum\Vimeet\Domain\View\Rooming\TotalStaysPerPeriod;

class HasRemainingOvernightTest extends TestCase
{
    public function testIsSatisfiedByTrue(): void
    {
        $arrival = new \DateTime('2018-12-19');
        $departure = new \DateTime('2018-12-21');
        $accommodation = $this->prophesize(Accommodation::class);
        $accommodation
            ->getOvernightCapacities()
            ->shouldBeCalled()
            ->willReturn([
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-19'), 10),
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-20'), 10),
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-21'), 10),
            ])
        ;

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository
            ->getTotalStaysByAccommodationPeriod($accommodation->reveal())
            ->shouldBeCalled()
            ->willReturn([
                new TotalStaysPerPeriod(new \DateTime('2018-12-19'), new \DateTime('2018-12-20'), 1),
                new TotalStaysPerPeriod(new \DateTime('2018-12-20'), new \DateTime('2018-12-21'), 2),
                new TotalStaysPerPeriod(new \DateTime('2018-12-19'), new \DateTime('2018-12-21'), 3),
            ]);

        $hasRemainingOvernight = new HasRemainingOvernight($stayRepository->reveal());
        $this->assertTrue($hasRemainingOvernight->isSatisfiedBy($accommodation->reveal(), $arrival, $departure));
    }

    public function testIsSatisfiedByFalse(): void
    {
        $arrival = new \DateTime('2018-12-19');
        $departure = new \DateTime('2018-12-21');
        $accommodation = $this->prophesize(Accommodation::class);
        $accommodation
            ->getOvernightCapacities()
            ->shouldBeCalled()
            ->willReturn([
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-19'), 10),
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-20'), 5),
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-21'), 10),
            ])
        ;

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository
            ->getTotalStaysByAccommodationPeriod($accommodation->reveal())
            ->shouldBeCalled()
            ->willReturn([
                new TotalStaysPerPeriod(new \DateTime('2018-12-19'), new \DateTime('2018-12-20'), 1),
                new TotalStaysPerPeriod(new \DateTime('2018-12-20'), new \DateTime('2018-12-21'), 2),
                new TotalStaysPerPeriod(new \DateTime('2018-12-19'), new \DateTime('2018-12-21'), 3),
            ]);

        $hasRemainingOvernight = new HasRemainingOvernight($stayRepository->reveal());
        $this->assertFalse($hasRemainingOvernight->isSatisfiedBy($accommodation->reveal(), $arrival, $departure));
    }

    public function test_accommodation_has_not_overnight_for_a_day(): void
    {
        $arrival = new \DateTime('2018-12-15');
        $departure = new \DateTime('2018-12-18');
        $accommodation = $this->prophesize(Accommodation::class);
        $accommodation
            ->getOvernightCapacities()
            ->shouldBeCalled()
            ->willReturn([
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-16'), 10),
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-17'), 10),
                new AccommodationOvernightCapacity($accommodation->reveal(), new \DateTime('2018-12-18'), 10),
            ])
        ;

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository
            ->getTotalStaysByAccommodationPeriod($accommodation->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $hasRemainingOvernight = new HasRemainingOvernight($stayRepository->reveal());
        $this->assertFalse($hasRemainingOvernight->isSatisfiedBy($accommodation->reveal(), $arrival, $departure));
    }
}
