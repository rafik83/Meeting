<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\EventView;

class ForgottenPassword
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var EventView
     */
    public $eventView;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param EventView $eventView
     * @param string    $locale
     */
    public function __construct(EventView $eventView, $locale)
    {
        $this->eventView = $eventView;
        $this->locale    = $locale;
    }
}

