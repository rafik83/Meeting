<?php

/*
 * This file is part of the Proxmimum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as ProximumEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class RegisteredEvent extends Event
{
    /**
     * @var ProximumEvent
     */
    private $event;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * RegisteredEvent constructor.
     *
     * @param ProximumEvent $event
     * @param User          $user
     * @param Participant   $participant
     * @param string        $locale
     */
    public function __construct(ProximumEvent $event, User $user, Participant $participant, $locale)
    {
        $this->event       = $event;
        $this->user        = $user;
        $this->locale      = $locale;
        $this->participant = $participant;
    }

    /**
     * @return ProximumEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }
}
