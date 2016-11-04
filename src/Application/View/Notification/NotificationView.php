<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Notification;

use DateTime;

class NotificationView
{
    /**
     * @var DateTime
     */
    public $createdAt;

    /**
     * @var string
     */
    public $category;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $link;

    /**
     * @var string
     */
    public $priority;

    /**
     * NotificationView constructor.
     *
     * @param DateTime $createdAt
     * @param string   $category
     * @param string   $description
     * @param string   $link
     * @param string   $priority
     */
    public function __construct(DateTime $createdAt, $category, $description, $link, $priority)
    {
        $this->createdAt   = $createdAt;
        $this->category    = $category;
        $this->description = $description;
        $this->link        = $link;
        $this->priority    = $priority;
    }
}
