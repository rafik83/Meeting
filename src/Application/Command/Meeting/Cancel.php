<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class Cancel
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var User
     */
    public $user;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var string
     */
    public $message;

    /**
     * Cancel constructor.
     *
     * @param Meeting            $meeting
     * @param User               $user
     * @param \DateTimeInterface $date
     */
    public function __construct(Meeting $meeting, User $user, \DateTimeInterface $date)
    {
        $this->meeting = $meeting;
        $this->user    = $user;
        $this->date    = $date;
    }
}
