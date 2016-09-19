<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Register;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class RegisterNewUser
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
    public $locale;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Type
     */
    public $type;

    /**
     * @param string $email
     * @param string $locale
     * @param Event  $event
     * @param Type   $type
     */
    public function __construct($email, $locale, Event $event, Type $type)
    {
        $this->email  = $email;
        $this->locale = $locale;
        $this->event  = $event;
        $this->type   = $type;
    }
}
