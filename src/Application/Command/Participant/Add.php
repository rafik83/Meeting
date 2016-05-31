<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;

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
     * @var Participant
     */
    public $participant;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @var EventView
     */
    public $eventView;

    /**
     * @param Sheet     $sheet
     * @param EventView $eventView
     * @param string    $locale
     */
    public function __construct(Sheet $sheet, EventView $eventView, $locale)
    {
        $this->sheet     = $sheet;
        $this->eventView = $eventView;
        $this->locale    = $locale;
        $this->owner     = false;
    }
}
