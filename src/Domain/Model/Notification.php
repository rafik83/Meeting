<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

class Notification
{
    /**
     * @var int
     */
    private $id;

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
     * @var DateTimeInterface
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
     * @param User              $emitter
     * @param User              $recipient
     * @param DateTimeInterface $createdAt
     * @param string            $action
     */
    public function __construct(User $emitter, User $recipient, DateTimeInterface $createdAt, $action)
    {
        $this->emitter   = $emitter;
        $this->recipient = $recipient;
        $this->createdAt = $createdAt;
        $this->action    = $action;
        $this->view      = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return boolean
     */
    public function isView()
    {
        return $this->view;
    }

    /**
     * @return DateTimeInterface
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
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
