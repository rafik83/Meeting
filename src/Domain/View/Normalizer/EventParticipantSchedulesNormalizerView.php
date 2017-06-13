<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Normalizer;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class EventParticipantSchedulesNormalizerView
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @param Event $event
     * @param Admin $admin
     */
    public function __construct(Event $event, Admin $admin)
    {
        $this->event = $event;
        $this->admin = $admin;
    }
}
