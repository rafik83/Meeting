<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class ConfigureDates implements Command
{
    /** @var Event */
    public $event;

    /** @var \DateTimeInterface|null "la date de mise en ligne du catalogue" */
    public $catalogOnlineDate;

    /** @var \DateTimeInterface|null "la date d'ouverture des inscriptions au s-event" */
    public $happeningsOpenDate;

    /** @var \DateTimeInterface|null "la date de publication des agendas définitifs (RDV)" */
    public $schedulePublishDate;

    /** @var null|\DateTimeInterface "la date d'activation des notifications SMS" */
    public $smsActivationDate;

    /** @var null|\DateTimeInterface "la date d'ouverture de l'agenda" */
    public $agendaOnlineDate;

    /** @var null|\DateTimeInterface "Date d'ouverture des inscriptions" */
    public $registrationOpenDate;

    /** @var null|\DateTimeInterface "Date de cloture des inscriptions" */
    public $registrationCloseDate;

    /** @var null|\DateTimeInterface "Active l'affichage du badge pour le participant" */
    public $enableBadgeForParticipantDate;

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
     * "la date d'activation du button de test visio"
     *
     * Date after which the visio test button is shown on the menu.
     *
     * @var \DateTimeInterface|null
     */
    public $enableVisioTestMenuButtonDate;

    /** @var \DateTimeInterface|null "Date de d'ouverture du networking" */
    public $networkingOpenDate;

    /** @var \DateTimeInterface|null "Date de cloture du networking" */
    public $networkingCloseDate;

    /**
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
        $this->enableBadgeForParticipantDate    = $event->getConfiguration()->getEnableBadgeForParticipantDate();
        $this->enableVisioTestMenuButtonDate    = $event->getConfiguration()->getEnableVisioTestMenuButtonDate();
        $this->networkingOpenDate               = $event->getConfiguration()->getNetworkingOpenDate();
        $this->networkingCloseDate              = $event->getConfiguration()->getNetworkingCloseDate();
    }

    public function addCatalogOnlineDateButEventHasNoDate(): bool
    {
        if ($this->event->hasDay()) {
            return true;
        }

        return $this->catalogOnlineDate === null;
    }
}
