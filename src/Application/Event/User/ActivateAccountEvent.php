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
use Proximum\Vimeet\Domain\Model\Sheet;
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
     * @var ProximumEvent
     */
    private $event;

    /**
     * @var ActivateAccountToken
     */
    private $activateAccountToken;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @param User                 $user
     * @param ProximumEvent        $event
     * @param ActivateAccountToken $activateAccountToken
     * @param Sheet                $sheet
     */
    public function __construct(
        User $user,
        ProximumEvent $event,
        ActivateAccountToken $activateAccountToken,
        Sheet $sheet
    ) {
        $this->user                 = $user;
        $this->event                = $event;
        $this->activateAccountToken = $activateAccountToken;
        $this->sheet                = $sheet;
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
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }
}
