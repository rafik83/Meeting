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
     * @var \DateTimeInterface
     */
    public $catalogOnlineDate;

    /**
     * "la date d'ouverture des inscriptions au s-event"
     *
     * @var \DateTimeInterface
     */
    public $happeningsOpenDate;

    /**
     * "la date de publication des agendas définitifs (RDV)"
     *
     * @var \DateTimeInterface
     */
    public $schedulePublishDate;

    /**
     * ConfigureDates constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event               = $event;
        $this->catalogOnlineDate   = $event->getConfiguration()->getCatalogOnlineDate();
        $this->happeningsOpenDate  = $event->getConfiguration()->getHappeningsOpenDate();
        $this->schedulePublishDate = $event->getConfiguration()->getSchedulePublishDate();
    }
}
