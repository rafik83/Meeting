<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\EventDispatcher\Event;

class ChangeMailAddressEvent extends Event
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
     * @var ChangeMailToken
     */
    private $changeMailToken;


    /**
     * @param User            $user
     * @param EventView       $eventView
     * @param ChangeMailToken $changeMailToken
     */
    public function __construct(User $user, EventView $eventView, ChangeMailToken $changeMailToken)
    {
        $this->user            = $user;
        $this->eventView       = $eventView;
        $this->changeMailToken = $changeMailToken;
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
     * @return ChangeMailToken
     */
    public function getChangeMailToken()
    {
        return $this->changeMailToken;
    }
}
