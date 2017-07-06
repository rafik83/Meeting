<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class ConfigureDates
{
    /**
     * @var Event
     */
    public $event;

    /**
     * "la date de mise en ligne du catalogue"
     *
     * @var \DateTimeInterface|null
     */
    public $catalogOnlineDate;

    /**
     * "la date d'ouverture des inscriptions au s-event"
     *
     * @var \DateTimeInterface|null
     */
    public $happeningsOpenDate;

    /**
     * "la date de publication des agendas définitifs (RDV)"
     *
     * @var \DateTimeInterface|null
     */
    public $schedulePublishDate;

    /**
     * "Bloquer la demande de rendez-vous"
     *
     * Date after which users can not request a meeting with other users on this event
     *
     * @var \DateTimeInterface|null
     */
    public $closeMeetingRequestDate;

    /**
     * "Bloquer les acceptations/refus des RDV"
     *
     * Date after which the state of the meeting requests can not be changed
     *
     * @var \DateTimeInterface|null
     */
    public $closeAnsweringMeetingRequestDate;

    /**
     * "la date d'activation des notifications SMS"
     *
     * @var null|\DateTimeInterface
     */
    public $smsActivationDate;

    /**
     * "la date d'ouverture de l'agenda"
     *
     * @var null|\DateTimeInterface
     */
    public $agendaOnlineDate;

    /**
     * "Date d'ouverture des inscriptions"
     *
     * @var null|\DateTimeInterface
     */
    public $registrationOpenDate;

    /**
     * "Date de cloture des inscriptions"
     *
     * @var null|\DateTimeInterface
     */
    public $registrationCloseDate;

    /**
     * ConfigureDates constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event                            = $event;
        $this->catalogOnlineDate                = $event->getConfiguration()->getCatalogOnlineDate();
        $this->happeningsOpenDate               = $event->getConfiguration()->getHappeningsOpenDate();
        $this->schedulePublishDate              = $event->getConfiguration()->getSchedulePublishDate();
        $this->closeMeetingRequestDate          = $event->getConfiguration()->getCloseMeetingRequestDate();
        $this->closeAnsweringMeetingRequestDate = $event->getConfiguration()->getCloseAnsweringMeetingRequestDate();
        $this->smsActivationDate                = $event->getConfiguration()->getSmsActivationDate();
        $this->agendaOnlineDate                 = $event->getConfiguration()->getAgendaOnlineDate();
        $this->registrationOpenDate             = $event->getConfiguration()->getRegistrationOpenDate();
        $this->registrationCloseDate            = $event->getConfiguration()->getRegistrationCloseDate();
    }
}
