<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Command\Unavailability\Add;
use Proximum\Vimeet\Application\Command\Unavailability\AddHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class AddUnavailabilityHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event                 = new Event();
        $type                  = new Type($event);
        $sheet                 = new Sheet($event, $type, [], []);
        $user                  = new User('test@test.com', '__SALT__', 'password', 'fr');
        $participant           = new Participant($sheet, $user, [], true, true);
        $command               = new Add();
        $command->from         = new \DateTime('2015-11-25 10:00:00');
        $command->to           = new \DateTime('2015-11-25 14:00:00');
        $command->participants = [$participant];

        $expectedUnavailability = new Unavailability(
            $participant,
            new \DateTime('2015-11-25 10:00:00'),
            new \DateTime('2015-11-25 14:00:00')
        );

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->getOverlapUnavailabilities($expectedUnavailability)->shouldBeCalled()->willReturn([]);
        $unavailabilityRepository->add($expectedUnavailability)->shouldBeCalled();

        $handler = new AddHandler($unavailabilityRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleWithMerge()
    {
        $event                 = new Event();
        $type                  = new Type($event);
        $sheet                 = new Sheet($event, $type, [], []);
        $user                  = new User('test@test.com', '__SALT__', 'password', 'fr');
        $participant           = new Participant($sheet, $user, [], true, true);
        $command               = new Add();
        $command->from         = new \DateTime('2015-11-25 10:00:00');
        $command->to           = new \DateTime('2015-11-25 14:00:00');
        $command->participants = [$participant];

        $unavailability1 = new Unavailability(
            $participant,
            new \DateTime('2015-11-25 09:00:00'),
            new \DateTime('2015-11-25 11:00:00')
        );

        $unavailability2 = new Unavailability(
            $participant,
            new \DateTime('2015-11-25 13:00:00'),
            new \DateTime('2015-11-25 15:00:00')
        );

        $createdUnavailability = new Unavailability(
            $participant,
            new \DateTime('2015-11-25 10:00:00'),
            new \DateTime('2015-11-25 14:00:00')
        );

        $mergedUnavailability = new Unavailability(
            $participant,
            new \DateTime('2015-11-25 09:00:00'),
            new \DateTime('2015-11-25 15:00:00')
        );

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->getOverlapUnavailabilities($createdUnavailability)->shouldBeCalled()->willReturn([$unavailability1, $unavailability2]);
        $unavailabilityRepository->remove($unavailability1)->shouldBeCalled();
        $unavailabilityRepository->remove($unavailability2)->shouldBeCalled();
        $unavailabilityRepository->add($mergedUnavailability)->shouldBeCalled();

        $handler = new AddHandler($unavailabilityRepository->reveal());
        $handler->handle($command);
    }
}
