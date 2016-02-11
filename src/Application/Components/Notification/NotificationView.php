<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Notification;

class NotificationView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $message;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var bool
     */
    public $read;

    /**
     * @var string
     */
    public $url;

    /**
     * NotificationView constructor.
     *
     * @param int                $id
     * @param string             $action
     * @param string             $message
     * @param \DateTimeInterface $date
     * @param bool               $read
     * @param string             $url
     */
    public function __construct($id, $action, $message, \DateTimeInterface $date, $read, $url)
    {
        $this->id      = $id;
        $this->action  = $action;
        $this->message = $message;
        $this->date    = $date;
        $this->read    = $read;
        $this->url     = $url;
    }
}
