<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail;

use Proximum\Vimeet\Application\Components\Mail\Mail;

class ChangeNewMailAddressMail extends Mail
{
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
     * @param string $token
     */
    public function __construct($sender, $receiver, $template, $messageId, $locale, $token)
    {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
