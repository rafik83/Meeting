<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Event;

class ChangeOldMailAddressMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.changeMailOld.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:ChangeMail/oldMail.html.twig';

    /**
     * @var string
     */
    protected $messageId = 'change_mail_old';

    /**
     * @var string
     */
    private $newMail;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param string $newMail
     */
    public function __construct(Event $event, $sender, $receiver, $locale, $newMail)
    {
        parent::__construct($sender, $receiver, $locale, null, null, $event);
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
