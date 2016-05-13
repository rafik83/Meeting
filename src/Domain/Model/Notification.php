<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class Notification
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var User
     */
    private $emitter;

    /**
     * @var User
     */
    private $recipient;

    /**
     * @var bool
     */
    private $view;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $url;

    /**
     * @param Event              $event
     * @param User               $emitter
     * @param User               $recipient
     * @param \DateTimeInterface $createdAt
     * @param string             $action
     * @param string             $message
     * @param string             $url
     */
    public function __construct(Event $event, User $emitter, User $recipient, \DateTimeInterface $createdAt, $action, $message, $url = null)
    {
        $this->event     = $event;
        $this->emitter   = $emitter;
        $this->recipient = $recipient;
        $this->createdAt = $createdAt;
        $this->action    = $action;
        $this->message   = $message;
        $this->view      = false;
        $this->message   = $message;
        $this->url       = $url;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return bool
     *
     * @deprecated Use is read instead
     */
    public function isView()
    {
        return $this->view;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        return (bool) $this->view;
    }

    /**
     * @return Notification
     */
    public function markAsRead()
    {
        $this->view = true;

        return $this;
    }
}
