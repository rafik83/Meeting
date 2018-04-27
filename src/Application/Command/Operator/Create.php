<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class Create
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var Admin
     */
    public $organizer;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var Event[]
     */
    public $events;

    /**
     * @param Admin              $organizer
     * @param \DateTimeInterface $date
     */
    public function __construct(Admin $organizer, \DateTimeInterface $date)
    {
        $this->organizer = $organizer;
        $this->password  = substr(md5(uniqid()), 0, 8);
        $this->date      = $date;
    }
}
