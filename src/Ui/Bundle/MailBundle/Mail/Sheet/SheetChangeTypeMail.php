<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class SheetChangeTypeMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.sheet.change_type.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Sheet/changeType.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::SHEET_CHANGED_TYPE;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $toTypeTitle;

    /**
     * @var string
     */
    private $fromTypeTitle;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = true;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param User   $user
     * @param string $fromTypeTitle
     * @param string $toTypeTitle
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        User $user,
        $fromTypeTitle,
        $toTypeTitle
    ) {
        parent::__construct($sender, $receiver, $locale, null, null, $event);

        $this->user          = $user;
        $this->fromTypeTitle = $fromTypeTitle;
        $this->toTypeTitle   = $toTypeTitle;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getFromTypeTitle()
    {
        return $this->fromTypeTitle;
    }

    /**
     * @return string
     */
    public function getToTypeTitle()
    {
        return $this->toTypeTitle;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%event%' => $this->getEvent()->getTitle(),
        ];
    }
}
