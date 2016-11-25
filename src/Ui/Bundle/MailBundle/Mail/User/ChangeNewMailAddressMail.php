<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\View\Mail\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;

class ChangeNewMailAddressMail extends UserMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.changeMailNew.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:ChangeMail/newMail.html.twig';

    /**
     * @var string
     */
    protected $messageId = 'change_mail_new';

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param string              $token
     * @param ParticipantInfoView $participantInfo
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $token,
        ParticipantInfoView $participantInfo
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfo);

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
