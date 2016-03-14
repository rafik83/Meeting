<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\Mail;

class ResetPasswordMail extends Mail
{
    /**
     * @var string
     */
    private $eventTitle;

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param string $eventTitle
     * @param string $token
     */
    public function __construct($sender, $receiver, $template, $messageId, $locale, $eventTitle, $token)
    {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);
        $this->eventTitle = $eventTitle;
        $this->token      = $token;
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
    public function getToken()
    {
        return $this->token;
    }
}
