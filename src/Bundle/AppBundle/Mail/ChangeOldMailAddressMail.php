<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Mail;

use Proximum\Vimeet\Application\Components\Mail\Mail;

class ChangeOldMailAddressMail extends Mail
{
    /**
     * @var string
     */
    private $newMail;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param string $newMail
     */
    public function __construct($sender, $receiver, $template, $messageId, $locale, $newMail)
    {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);
        $this->newMail = $newMail;
    }

    /**
     * @return string
     */
    public function getNewMail()
    {
        return $this->newMail;
    }
}
