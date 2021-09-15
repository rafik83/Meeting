<?php

namespace Proximum\Vimeet\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UnavailabilityTest extends TestCase
{
    public static function provideDateTime()
    {
        return [
            [
                new \DateTime('2015-11-25 12:00:00'),
                new \DateTime('2015-11-25 14:00:00'),
                new \DateTime('2015-11-25 10:00:00'),
                new \DateTime('2015-11-25 13:00:00'),
                new \DateTime('2015-11-25 10:00:00'),
                new \DateTime('2015-11-25 14:00:00'),
            ],
            [
                new \DateTime('2015-11-25 12:00:00'),
                new \DateTime('2015-11-25 14:00:00'),
                new \DateTime('2015-11-25 10:00:00'),
                new \DateTime('2015-11-25 16:00:00'),
                new \DateTime('2015-11-25 10:00:00'),
                new \DateTime('2015-11-25 16:00:00'),
            ],
            [
                new \DateTime('2015-11-25 12:00:00'),
                new \DateTime('2015-11-25 14:00:00'),
                new \DateTime('2015-11-25 13:00:00'),
                new \DateTime('2015-11-25 16:00:00'),
                new \DateTime('2015-11-25 12:00:00'),
                new \DateTime('2015-11-25 16:00:00'),
            ],
            [
                new \DateTime('2015-11-25 12:00:00'),
                new \DateTime('2015-11-25 14:00:00'),
                new \DateTime('2015-11-25 13:30:00'),
                new \DateTime('2015-11-25 13:40:00'),
                new \DateTime('2015-11-25 12:00:00'),
                new \DateTime('2015-11-25 14:00:00'),
            ],
        ];
    }

    /**
     * @dataProvider provideDateTime
     */
    public function testMerge(\DateTime $a, \DateTime $b, \DateTime $c, \DateTime $d, \DateTime $e, \DateTime $f)
    {
        $event = EventFactory::createEvent();
        $user  = new User('test@test.com', '__SALT__', 'password', 'fr');

        $expected      = new Unavailability($user, $event, $e, $f);
        $unavailability = new Unavailability($user, $event, $a, $b);
        $unavailability->merge(new Unavailability($user, $event, $c, $d));

        $this->assertEquals($expected, $unavailability);
    }
}
