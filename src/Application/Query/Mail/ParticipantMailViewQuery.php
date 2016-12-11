<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Mail;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantMailViewQuery
{
    /**
     * @var Sheet|null
     */
    public $sheet;

    /**
     * @var User
     */
    public $user;

    /**
     * ParticipantMailViewQuery constructor.
     *
     * @param Sheet|null $sheet
     * @param User       $user
     */
    public function __construct(Sheet $sheet = null, User $user)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
    }
}
