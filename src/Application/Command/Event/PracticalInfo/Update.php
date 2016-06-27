<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\PracticalInfo;

use Proximum\Vimeet\Domain\Model;

class Update
{
    /**
     * @var Model\Event
     */
    public $event;

    /**
     * @var string
     */
    public $organiserName;

    /**
     * @var string
     */
    public $organiserEmail;

    /**
     * @var string
     */
    public $contactFirstName;

    /**
     * @var string
     */
    public $contactLastName;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $website;

    /**
     * Update constructor.
     * @param Model\Event $event
     */
    public function __construct(Model\Event $event)
    {
        $this->event            = $event;
        $this->organiserName    = $event->getOrganiserName();
        $this->organiserEmail   = $event->getOrganiserEmail();
        $this->contactFirstName = $event->getConfiguration()->getContactFirstName();
        $this->contactLastName  = $event->getConfiguration()->getContactLastName();
        $this->phone            = $event->getConfiguration()->getPhone();
        $this->website          = $event->getConfiguration()->getWebsite();
    }
}