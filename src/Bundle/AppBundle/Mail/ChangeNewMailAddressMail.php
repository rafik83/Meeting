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
use Proximum\Vimeet\Domain\Model\Event;

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
