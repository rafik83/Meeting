<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Command\Unavailability\Update;
use Proximum\Vimeet\Application\Command\Unavailability\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Context
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $user        = new User('email@email.com', 'salt', 'password', 'fr');
        $participant = new Participant($sheet, $user, [], true, true);

        // Actual unavailability
        $unavailability = new Unavailability($participant, new \DateTime('2016-01-15 09:00:00'), new \DateTime('2016-01-15 11:00:00'));

        // Command
        $command       = new Update($unavailability);
        $command->from = new \DateTime('2016-01-15 09:00:00');
        $command->to   = new \DateTime('2016-01-15 13:00:00');

        // Expected
        $expected = new Unavailability($participant, new \DateTime('2016-01-15 09:00:00'), new \DateTime('2016-01-15 13:00:00'));

        // Mock
        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->getOverlapUnavailabilities($expected)->shouldBeCalled()->willReturn([]);
        $unavailabilityRepository->remove()->shouldNotBeCalled();
        $unavailabilityRepository->set($expected)->shouldBeCalled();

        // Handler
        $handler = new UpdateHandler($unavailabilityRepository->reveal());
        $handler->handle($command);
    }


    public function testHandleWithMerge()
    {
        // Context
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $user        = new User('email@email.com', 'salt', 'password', 'fr');
        $participant = new Participant($sheet, $user, [], true, true);


        //Actual unavailability
        $unavailability1 = new Unavailability($participant, new \DateTime('2016-01-15 09:00:00'), new \DateTime('2016-01-15 13:00:00'));
        $unavailability2 = new Unavailability($participant, new \DateTime('2016-01-15 12:00:00'), new \DateTime('2016-01-15 17:00:00'));

        //Command
        $command       = new Update($unavailability1);
        $command->from = new \DateTime('2016-01-15 08:00:00');
        $command->to   = new \DateTime('2016-01-15 13:00:00');

        //Expected unavailability
        $expected1 = new Unavailability($participant, new \DateTime('2016-01-15 08:00:00'), new \DateTime('2016-01-15 13:00:00'));
        $expected2 = new Unavailability($participant, new \DateTime('2016-01-15 08:00:00'), new \DateTime('2016-01-15 17:00:00'));

        //Mock
        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->getOverlapUnavailabilities($expected1)->shouldBeCalled()->willReturn([$unavailability2]);
        $unavailabilityRepository->remove($unavailability2)->shouldBeCalled();
        $unavailabilityRepository->set($expected2)->shouldBecalled();

        //Handler
        $handler = new UpdateHandler($unavailabilityRepository->reveal());
        $handler->handle($command);

    }
}
