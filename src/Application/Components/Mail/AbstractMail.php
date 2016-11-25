<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Mail;

class AbstractMail
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var array
     */
    protected $subjectParameters = [];

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $receiver;

    /**
     * AbstractMail constructor.
     *
     * @param string $sender
     * @param string $receiver
     * @param        $locale
     */
    public function __construct($sender, $receiver, $locale)
    {
        $this->sender   = $sender;
        $this->receiver = $receiver;
        $this->locale   = $locale;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getSubjectParameters()
    {
        return $this->subjectParameters;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return boolean
     */
    public function sendToEmailTeam()
    {
        return $this->sendToEmailTeam;
    }
}
