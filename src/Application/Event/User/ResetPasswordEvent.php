<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as EventModel;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Symfony\Component\EventDispatcher\Event;

class ResetPasswordEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var EventModel
     */
    private $event;

    /**
     * @var ForgottenPasswordToken
     */
    private $forgottenPasswordToken;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param User                   $user
     * @param EventModel             $event
     * @param ForgottenPasswordToken $forgottenPasswordToken
     * @param string                 $locale
     */
    public function __construct(User $user, EventModel $event, ForgottenPasswordToken $forgottenPasswordToken, $locale)
    {
        $this->user                   = $user;
        $this->event                  = $event;
        $this->forgottenPasswordToken = $forgottenPasswordToken;
        $this->locale                 = $locale;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return EventModel
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return ForgottenPasswordToken
     */
    public function getForgottenPasswordToken()
    {
        return $this->forgottenPasswordToken;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
