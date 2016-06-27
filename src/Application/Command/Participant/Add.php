<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Event;

class Add
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Sheet  $sheet
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Sheet $sheet, Event $event, $locale)
    {
        $this->sheet  = $sheet;
        $this->event  = $event;
        $this->locale = $locale;
        $this->owner  = false;
    }
}
