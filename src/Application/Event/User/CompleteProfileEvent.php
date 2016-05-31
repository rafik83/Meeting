<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\EventDispatcher\Event;

class CompleteProfileEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var EventView
     */
    private $eventView;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @param User        $user
     * @param EventView   $eventView
     * @param Participant $participant
     * @param string      $locale
     */
    public function __construct(User $user, EventView $eventView, Participant $participant, $locale)
    {
        $this->user        = $user;
        $this->eventView   = $eventView;
        $this->participant = $participant;
        $this->locale      = $locale;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return EventView
     */
    public function getEventView()
    {
        return $this->eventView;
    }

    /**
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
