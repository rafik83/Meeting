<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SendEmailingByType
{
    /** @var Event */
    public $event;

    /** @var string */
    public $messageId;

    /** @var Sheet[] */
    public $sheets;

    /** @var bool */
    public $sendEmailToTeam;

    public function __construct(Event $event, string $messageId, array $sheets, bool $sendEmailToTeam)
    {
        $this->event = $event;
        $this->messageId = $messageId;
        $this->sheets = $sheets;
        $this->sendEmailToTeam = $sendEmailToTeam;
    }
}
