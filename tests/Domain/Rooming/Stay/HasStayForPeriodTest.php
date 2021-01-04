<?php

namespace Proximum\Vimeet\Tests\Domain\Rooming\Stay;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriod;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class HasStayForPeriodTest extends TestCase
{
    public function testIsNotSatisfiedBy()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $arrival = new \DateTime('2018-12-20');
        $departure = new \DateTime('2018-12-22');

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository
            ->getTimeRangeViewsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    new TimeRangeView(new \DateTime('2018-12-18'), new \DateTime('2018-12-20')),
                    new TimeRangeView(new \DateTime('2018-12-22'), new \DateTime('2018-12-23')),
                ]
            )
        ;

        $hasStayForPeriod = new HasStayForPeriod($stayRepository->reveal());
        $this->assertFalse(
            $hasStayForPeriod->isSatisfiedBy(
                $event->reveal(),
                $user->reveal(),
                $arrival,
                $departure
            )
        );
    }

    public function testIsSatisfiedBy()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $arrival = new \DateTime('2018-12-20');
        $departure = new \DateTime('2018-12-22');

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository
            ->getTimeRangeViewsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    new TimeRangeView(new \DateTime('2018-12-18'), new \DateTime('2018-12-20')),
                    new TimeRangeView(new \DateTime('2018-12-21'), new \DateTime('2018-12-23')),
                ]
            )
        ;

        $hasStayForPeriod = new HasStayForPeriod($stayRepository->reveal());
        $this->assertTrue(
            $hasStayForPeriod->isSatisfiedBy(
                $event->reveal(),
                $user->reveal(),
                $arrival,
                $departure
            )
        );
    }

    public function testIsSatisfiedByWithPeriodIn()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $arrival = new \DateTime('2018-12-20');
        $departure = new \DateTime('2018-12-22');

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository
            ->getTimeRangeViewsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    new TimeRangeView(new \DateTime('2018-12-18'), new \DateTime('2018-12-23')),
                ]
            )
        ;

        $hasStayForPeriod = new HasStayForPeriod($stayRepository->reveal());
        $this->assertTrue(
            $hasStayForPeriod->isSatisfiedBy(
                $event->reveal(),
                $user->reveal(),
                $arrival,
                $departure
            )
        );
    }
}
