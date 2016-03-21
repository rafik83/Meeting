<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\TraceableInterface;
use Symfony\Component\EventDispatcher\Event;

class TraceEvent extends Event
{
    /**
     * @var TraceableInterface
     */
    private $traceable;

    /**
     * @var AbstractUser
     */
    private $user;

    /**
     * @var string
     */
    private $action;

    /**
     * @var DateTimeInterface
     */
    private $date;

    /**
     * @var string
     */
    private $comment;

    /**
     * @param TraceableInterface $traceable
     * @param string             $action
     * @param AbstractUser       $user
     * @param DateTimeInterface  $date
     * @param string             $comment
     */
    public function __construct(
        TraceableInterface $traceable,
        $action,
        AbstractUser $user,
        DateTimeInterface $date,
        $comment
    ) {
        $this->traceable = $traceable;
        $this->action    = $action;
        $this->user      = $user;
        $this->date      = $date;
        $this->comment   = $comment;
    }

    /**
     * @return TraceableInterface
     */
    public function getTraceable()
    {
        return $this->traceable;
    }

    /**
     * @return AbstractUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
