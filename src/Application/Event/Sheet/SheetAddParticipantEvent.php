<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher;

class SheetAddParticipantEvent extends EventDispatcher\Event
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var User
     */
    private $user;

    /**
     * @var User
     */
    private $guest;

    /**
     * SheetAddParticipantEvent constructor.
     *
     * @param Sheet $sheet
     * @param User  $guest
     * @param User  $user
     */
    public function __construct(Sheet $sheet, User $guest, User $user)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
        $this->guest = $guest;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return User
     */
    public function getGuest()
    {
        return $this->guest;
    }
}
