<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\EventDispatcher;

class ParticipantImportedEvent extends EventDispatcher\Event
{
    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTimeInterface
     */
    private $date;


    /**
     * ParticipantImportedEvent constructor.
     *
     * @param Admin $admin
     * @param Event $event
     * @param \DateTimeInterface $date
     */
    public function __construct(Admin $admin, Event $event, \DateTimeInterface $date)
    {
        $this->admin = $admin;
        $this->event = $event;
        $this->date  = $date;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

}
