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
use Proximum\Vimeet\Domain\Model\User;

class ChangeNewMailAddressMail extends Mail
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var User
     */
    private $user;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param string $token
     * @param User   $user
     */
    public function __construct($sender, $receiver, $template, $messageId, $locale, $token, User $user)
    {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);

        $this->token = $token;
        $this->user  = $user;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
