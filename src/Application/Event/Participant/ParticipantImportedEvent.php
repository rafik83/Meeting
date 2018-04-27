<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
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
     * @var Sheet[]
     */
    private $sheets;

    /**
     * ParticipantImportedEvent constructor.
     *
     * @param Admin              $admin
     * @param Event              $event
     * @param \DateTimeInterface $date
     * @param Sheet[]            $sheets
     */
    public function __construct(Admin $admin, Event $event, \DateTimeInterface $date, array $sheets)
    {
        $this->admin  = $admin;
        $this->event  = $event;
        $this->date   = $date;
        $this->sheets = $sheets;
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

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets;
    }
}
