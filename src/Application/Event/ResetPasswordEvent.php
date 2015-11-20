<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\Model\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Model\User;

class ResetPasswordEvent extends ApplicationEvent
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
     * @var ForgottenPasswordToken
     */
    private $forgottenPasswordToken;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param User                   $user
     * @param EventView              $eventView
     * @param ForgottenPasswordToken $forgottenPasswordToken
     * @param string                 $locale
     */
    public function __construct(User $user, EventView $eventView, ForgottenPasswordToken $forgottenPasswordToken, $locale)
    {
        $this->user                   = $user;
        $this->eventView              = $eventView;
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
     * @return EventView
     */
    public function getEventView()
    {
        return $this->eventView;
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
