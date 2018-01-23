<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class Update
{
    /** @var string */
    public $email;

    /** @var string */
    public $lastname;

    /** @var string */
    public $firstname;

    /** @var Admin */
    public $operator;

    /** @var Event[] */
    public $events;

    /** @var Event[] */
    public $allowedEventsByAdmin;

    /**
     * @param Admin   $operator
     * @param Event[] $allowedEventsByAdmin
     */
    public function __construct(Admin $operator, array $allowedEventsByAdmin)
    {
        $this->operator  = $operator;
        $this->email     = $operator->getEmail();
        $this->lastname  = $operator->getLastname();
        $this->firstname = $operator->getFirstname();
        $this->events    = $operator->getEvents()->toArray();
        $this->allowedEventsByAdmin = $allowedEventsByAdmin;
    }
}
