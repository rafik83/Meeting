<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Event\ConfigureDates;
use Proximum\Vimeet\Application\Command\Event\ConfigureDatesHandler;
use Proximum\Vimeet\Application\Event\Event\KeyDatesUpdatedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConfigureDatesHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    public function setUp(): void
    {
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
    }

    public function testHandle(): void
    {
        $event = EventFactory::createEvent();

        $catalogOnlineDate = new \DateTime('2016-06-23 12:00:00');
        $happeningsOpenDate = new \DateTime('2016-06-21 12:00:00');
        $schedulePublishDate = new \DateTime('2016-06-30 12:00:00');
        $closeMeetingRequestDate = new \DateTime('2016-07-08 12:00:00');
        $closeAnsweringMeetingRequestDate = new \DateTime('2016-07-09 12:00:00');
        $smsActivationDate = new \DateTime('2016-06-09 12:00:00');
        $registrationOpenDate = new \DateTime('2016-06-09 12:00:00');
        $registrationCloseDate = new \DateTime('2016-06-10 12:00:00');
        $enableBadgeForParticipantDate = new \DateTime('2016-06-10 12:00:00');
        $enableVisioTestMenuButtonDate = new \DateTime('2020-06-12 12:00:00');

        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->setDates(
            $catalogOnlineDate,
            $happeningsOpenDate,
            $schedulePublishDate,
            $closeMeetingRequestDate,
            $closeAnsweringMeetingRequestDate,
            $smsActivationDate,
            null,
            $registrationOpenDate,
            $registrationCloseDate,
            $enableBadgeForParticipantDate,
            $enableVisioTestMenuButtonDate
        );

        $this->eventRepository->set($expectedEvent)->shouldBeCalled();
        $this->eventDispatcher->dispatch(Events::EVENT_KEY_DATES_UPDATED, new KeyDatesUpdatedEvent($expectedEvent));

        $command = new ConfigureDates($event);
        $command->catalogOnlineDate = $catalogOnlineDate;
        $command->happeningsOpenDate = $happeningsOpenDate;
        $command->schedulePublishDate = $schedulePublishDate;
        $command->closeMeetingRequestDate = $closeMeetingRequestDate;
        $command->closeAnsweringMeetingRequestDate = $closeAnsweringMeetingRequestDate;
        $command->smsActivationDate = $smsActivationDate;
        $command->registrationOpenDate = $registrationOpenDate;
        $command->registrationCloseDate = $registrationCloseDate;
        $command->enableBadgeForParticipantDate = $enableBadgeForParticipantDate;
        $command->enableVisioTestMenuButtonDate = $enableVisioTestMenuButtonDate;

        $handler = new ConfigureDatesHandler($this->eventRepository->reveal(), $this->eventDispatcher->reveal());
        $handler->handle($command);
    }

    public function testHandleWithNullableDates(): void
    {
        $event = EventFactory::createEvent();

        $catalogOnlineDate = new \DateTime('2016-06-23 12:00:00');
        $schedulePublishDate = new \DateTime('2016-06-30 12:00:00');
        $closeAnsweringMeetingRequestDate = new \DateTime('2016-07-09 12:00:00');

        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->setDates(
            $catalogOnlineDate,
            null,
            $schedulePublishDate,
            null,
            $closeAnsweringMeetingRequestDate,
            null
        );

        $this->eventRepository->set($expectedEvent)->shouldBeCalled();
        $this->eventDispatcher->dispatch(Events::EVENT_KEY_DATES_UPDATED, new KeyDatesUpdatedEvent($expectedEvent));

        $command = new ConfigureDates($event);
        $command->catalogOnlineDate = $catalogOnlineDate;
        $command->happeningsOpenDate = null;
        $command->schedulePublishDate = $schedulePublishDate;
        $command->closeMeetingRequestDate = null;
        $command->closeAnsweringMeetingRequestDate = $closeAnsweringMeetingRequestDate;
        $command->smsActivationDate = null;
        $command->registrationOpenDate = null;
        $command->registrationCloseDate = null;
        $command->enableBadgeForParticipantDate = null;
        $command->enableVisioTestMenuButtonDate = null;

        $handler = new ConfigureDatesHandler($this->eventRepository->reveal(), $this->eventDispatcher->reveal());
        $handler->handle($command);
    }
}
