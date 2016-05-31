<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\Mail;

class CompleteProfileMail extends Mail
{
    /**
     * @var string
     */
    private $eventTitle;

    /**
     * @var int
     */
    private $participantId;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param string $eventTitle
     * @param int    $participantId
     */
    public function __construct($sender, $receiver, $template, $messageId, $locale, $eventTitle, $participantId)
    {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);

        $this->eventTitle    = $eventTitle;
        $this->participantId = $participantId;
    }

    /**
     * @return string
     */
    public function getEventTitle()
    {
        return $this->eventTitle;
    }

    /**
     * @return string
     */
    public function getParticipantId()
    {
        return $this->participantId;
    }
}
