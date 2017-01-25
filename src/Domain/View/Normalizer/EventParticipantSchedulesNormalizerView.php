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
    public $user;

    /**
     * @param Event $event
     */
    public function __construct(Event $event, Admin $user)
    {
        $this->event = $event;
        $this->user  = $user;
    }
}
