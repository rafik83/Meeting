<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class AccessManager
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Event $event
     */
    public function openTheAgenda(Event $event)
    {
        $event->getConfiguration()->setDates(
            $event->getConfiguration()->getCatalogOnlineDate(),
            $event->getConfiguration()->getHappeningsOpenDate(),
            $event->getConfiguration()->getSchedulePublishDate(),
            $event->getConfiguration()->getCloseMeetingRequestDate(),
            $event->getConfiguration()->getCloseAnsweringMeetingRequestDate(),
            $event->getConfiguration()->getSmsActivationDate(),
            new \DateTime('2000-01-01 08:00:00')
        );

        $this->eventRepository->set($event);
    }

    /**
     * @param Event $event
     */
    public function publishMeetings(Event $event)
    {
        $event->getConfiguration()->setDates(
            $event->getConfiguration()->getCatalogOnlineDate(),
            $event->getConfiguration()->getHappeningsOpenDate(),
            new \DateTime('2000-01-01 08:00:00'),
            $event->getConfiguration()->getCloseMeetingRequestDate(),
            $event->getConfiguration()->getCloseAnsweringMeetingRequestDate(),
            $event->getConfiguration()->getsmsActivationDate(),
            $event->getConfiguration()->getAgendaOnlineDate()
        );

        $this->eventRepository->set($event);
    }

    /**
     * @param \DateTime $dateTime
     * @param Event     $event
     */
    public function setRegistrationOpenDate(\DateTime $dateTime, Event $event)
    {
        $event->getConfiguration()->setDates(
            $event->getConfiguration()->getCatalogOnlineDate(),
            $event->getConfiguration()->getHappeningsOpenDate(),
            $event->getConfiguration()->getSchedulePublishDate(),
            $event->getConfiguration()->getCloseMeetingRequestDate(),
            $event->getConfiguration()->getCloseAnsweringMeetingRequestDate(),
            $event->getConfiguration()->getsmsActivationDate(),
            $event->getConfiguration()->getAgendaOnlineDate(),
            $dateTime
        );

        $this->eventRepository->set($event);
    }

    /**
     * @param \DateTime $dateTime
     * @param Event     $event
     */
    public function setRegistrationCloseDate(\DateTime $dateTime, Event $event)
    {
        $event->getConfiguration()->setDates(
            $event->getConfiguration()->getCatalogOnlineDate(),
            $event->getConfiguration()->getHappeningsOpenDate(),
            $event->getConfiguration()->getSchedulePublishDate(),
            $event->getConfiguration()->getCloseMeetingRequestDate(),
            $event->getConfiguration()->getCloseAnsweringMeetingRequestDate(),
            $event->getConfiguration()->getsmsActivationDate(),
            $event->getConfiguration()->getAgendaOnlineDate(),
            null,
            $dateTime
        );

        $this->eventRepository->set($event);
    }

    public function setHappeningsOpenDate(\DateTime $dateTime, Event $event)
    {
        $event->getConfiguration()->setDates(
            $event->getConfiguration()->getCatalogOnlineDate(),
            $dateTime,
            $event->getConfiguration()->getSchedulePublishDate(),
            $event->getConfiguration()->getCloseMeetingRequestDate(),
            $event->getConfiguration()->getCloseAnsweringMeetingRequestDate(),
            $event->getConfiguration()->getsmsActivationDate(),
            $event->getConfiguration()->getAgendaOnlineDate(),
            $event->getConfiguration()->getRegistrationOpenDate(),
            $event->getConfiguration()->getRegistrationCloseDate()
        );

        $this->eventRepository->set($event);
    }

    /**
     * @param Event $event
     */
    public function openExternalCatalog(Event $event)
    {
        $event->setExternalCatalog(true);

        $this->eventRepository->set($event);
    }

    /**
     * @param Event     $event
     * @param \DateTime $datetime
     */
    public function openCatalog(Event $event, \DateTime $datetime)
    {
        $event->getConfiguration()->setDates($datetime);

        $this->eventRepository->set($event);
    }
}
