<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Command\Unavailability\Remove;
use Proximum\Vimeet\Application\Command\Unavailability\RemoveHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class RemoveTestHandler extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {

        // Context
        $event       = new Event();
        $schedule    = new Schedule($event, new \DateTime('2016-01-15 12:00:00'));
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], []);
        $user        = new User('email@email.com', 'salt', 'password', 'fr');
        $participant = new Participant($sheet, $user, [], true);

        //Actual unavailability
        $unavailability = new Unavailability($schedule, $participant, new \DateTime('2016-01-15 09:00:00'), new \DateTime('2016-01-15 11:00:00'));

        //Expected
        $remove = new Remove($unavailability);

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->remove($unavailability)->shouldBeCalled();

        $handler = new RemoveHandler($unavailabilityRepository->reveal());
        $handler->handle($remove);
    }
}
