<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\AbstractCustomizedMail;
use Proximum\Vimeet\Domain\Model\Event;

class AddParticipantCustomizedMail extends AbstractCustomizedMail
{
    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale,
        string $subject,
        string $content
    ) {
        parent::__construct($event, $sender, $receiver, $locale);

        $this->subject = $subject;
        $this->content = $content;
    }
}
