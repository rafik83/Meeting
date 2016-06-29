<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as ProximumEvent;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Symfony\Component\EventDispatcher\Event;

class ActivateAccountEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var User
     */
    private $sender;

    /**
     * @var ProximumEvent
     */
    private $event;

    /**
     * @var ActivateAccountToken
     */
    private $activateAccountToken;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param User                 $user
     * @param User                 $sender
     * @param ProximumEvent        $event
     * @param ActivateAccountToken $activateAccountToken
     * @param string               $locale
     */
    public function __construct(User $user, User $sender, ProximumEvent $event, ActivateAccountToken $activateAccountToken, $locale)
    {
        $this->user                 = $user;
        $this->event                = $event;
        $this->activateAccountToken = $activateAccountToken;
        $this->locale               = $locale;
        $this->sender               = $sender;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return ProximumEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return ActivateAccountToken
     */
    public function getActivateAccountToken()
    {
        return $this->activateAccountToken;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }
}
