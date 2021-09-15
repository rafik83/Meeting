<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UnavailabilityViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime('2016-10-12 12:00:00');
        $end   = new \DateTime('2016-10-12 14:00:00');
        $user  = UserFactory::create();
        $day   = new TimeRangeView(new \DateTime('2016-10-12 08:00:00'), new \DateTime('2016-10-12 20:00:00'));

        $unavailability = new Unavailability($user, $event, $begin, $end);
        $reflection     = new \ReflectionClass(Unavailability::class);
        $property       = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($unavailability, 1);
        $property->setAccessible(false);

        $handler = new UnavailabilityViewQueryHandler();
        $result  = $handler->handle(
            new UnavailabilityViewQuery(
                $unavailability,
                $event,
                $day
            )
        );

        // Expected
        $expected = new UnavailabilityView(
            1,
            new \DateTime('2016-10-12 12:00:00'),
            new \DateTime('2016-10-12 14:00:00'),
            'Europe/Paris',
            null,
            true,
            true
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithBeginUnavailabilityModified()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $day   = new TimeRangeView(new \DateTime('2016-10-12 10:00:00'), new \DateTime('2016-10-12 18:00:00'));

        $unavailability = new Unavailability(
            $user,
            $event,
            new \DateTime('2016-10-12 08:00:00'),
            new \DateTime('2016-10-12 12:00:00')
        );
        $reflection     = new \ReflectionClass(Unavailability::class);
        $property       = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($unavailability, 1);
        $property->setAccessible(false);

        $handler = new UnavailabilityViewQueryHandler();
        $result  = $handler->handle(
            new UnavailabilityViewQuery(
                $unavailability,
                $event,
                $day
            )
        );

        // Expected
        $expected = new UnavailabilityView(
            1,
            new \DateTime('2016-10-12 10:00:00'),
            new \DateTime('2016-10-12 12:00:00'),
            'Europe/Paris',
            null,
            true,
            true
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithEndUnavailabilityModified()
    {
        $event = EventFactory::createEvent();
        $user  = UserFactory::create();
        $day   = new TimeRangeView(new \DateTime('2016-10-12 10:00:00'), new \DateTime('2016-10-12 18:00:00'));

        $unavailability = new Unavailability(
            $user,
            $event,
            new \DateTime('2016-10-12 16:00:00'),
            new \DateTime('2016-10-12 20:00:00')
        );
        $reflection     = new \ReflectionClass(Unavailability::class);
        $property       = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($unavailability, 1);
        $property->setAccessible(false);

        $handler = new UnavailabilityViewQueryHandler();
        $result  = $handler->handle(
            new UnavailabilityViewQuery(
                $unavailability,
                $event,
                $day
            )
        );

        // Expected
        $expected = new UnavailabilityView(
            1,
            new \DateTime('2016-10-12 16:00:00'),
            new \DateTime('2016-10-12 18:00:00'),
            'Europe/Paris',
            null,
            true,
            true
        );

        $this->assertEquals($expected, $result);
    }
}
