<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Domain\Model;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;

class UnavailabilityTest extends \PHPUnit_Framework_TestCase
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
        $event                 = new Event();
        $type                  = new Type($event);
        $sheet                 = new Sheet($event, $type, [], [], new \DateTime());
        $user                  = new User('test@test.com', '__SALT__', 'password', 'fr');
        $participant           = new Participant($sheet, $user, [], true, true);

        $expected      = new Unavailability($participant, $e, $f);
        $unvailability = new Unavailability($participant, $a, $b);
        $unvailability->merge(new Unavailability($participant, $c, $d));

        $this->assertEquals($expected, $unvailability);
    }
}
