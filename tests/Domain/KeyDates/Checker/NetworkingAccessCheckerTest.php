<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class NetworkingAccessCheckerTest extends TestCase
{

    public function testIsNotAllowedIfNoOpeningAndClosingDateArerNotPovided()
    {

        $now  = new \DateTime();

        $openingDate =  null;
        $closingDate =  null;

        $configuration = $this->prophesize(Configuration::class);
        $configuration->getNetworkingCloseDate()->willReturn($closingDate);
        $configuration->getNetworkingOpenDate()->willReturn($openingDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->willReturn($configuration->reveal());


        $networkingAccessChecker = new NetworkingAccessChecker($now);

        $this->assertEquals(false, $networkingAccessChecker->allowedToAccess($event->reveal()));
    }


    public function testIsNotAllowedIfTheCurrentDateIsNotBetweenClosingAndOpeningDate()
    {
        /** 
         * Case $now is NOT between opening date and closing 
         */

        $now  = new \DateTime();

        $openingDate =  new \DateTime('2000-01-01T15:03:01.012345Z'); // opens on 2000-01-01
        $closingDate =  new \DateTime('2001-01-01T15:03:01.012345Z'); // closes on 2001-01-01

        $configuration = $this->prophesize(Configuration::class);
        $configuration->getNetworkingCloseDate()->willReturn($closingDate);
        $configuration->getNetworkingOpenDate()->willReturn($openingDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->willReturn($configuration->reveal());

        $networkingAccessChecker = new NetworkingAccessChecker($now);
        $this->assertEquals(false, $networkingAccessChecker->allowedToAccess($event->reveal()));
    }

    public function testIsAllowedIfTheCurrentDateIsBetweenClosingAndOpeningDate()
    {
        /** 
         * Case $now is between opening date and closing 
         */

        $now  = new \DateTime();

        $openingDate =  new \DateTime('2000-01-01T15:03:01.012345Z'); // opens on 2000-01-01
        $closingDate =  new \DateTime('2052-01-01T15:03:01.012345Z'); // closes on 2052-01-01

        $configuration = $this->prophesize(Configuration::class);
        $configuration->getNetworkingCloseDate()->willReturn($closingDate);
        $configuration->getNetworkingOpenDate()->willReturn($openingDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->willReturn($configuration->reveal());

        $networkingAccessChecker = new NetworkingAccessChecker($now);
        $this->assertEquals(true, $networkingAccessChecker->allowedToAccess($event->reveal()));
    }
}
