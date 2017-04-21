<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class CreateMessage
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $emailTemplate;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var Sheet[]
     */
    public $sheets;

    /**
     * @var bool
     */
    public $sendToEmailTeam = false;

    /**
     * Create constructor.
     *
     * @param Event   $event
     * @param Sheet[] $sheets
     * @param string  $name
     * @param string  $subject
     * @param string  $emailTemplate
     * @param bool    $sendToEmailTeam
     */
    public function __construct(Event $event, array $sheets, $name, $subject, $emailTemplate, $sendToEmailTeam = false)
    {
        $this->event           = $event;
        $this->subject         = $subject;
        $this->emailTemplate   = $emailTemplate;
        $this->name            = $name;
        $this->sheets          = $sheets;
        $this->sendToEmailTeam = $sendToEmailTeam;
    }
}
