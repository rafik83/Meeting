<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Notification;

use DateTimeInterface;

class NotificationView
{
    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * @var string
     */
    public $icon;

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
     * @var array
     */
    public $parameters;

    /**
     * NotificationView constructor.
     *
     * @param DateTimeInterface $createdAt
     * @param                   $icon
     * @param string            $category
     * @param string            $description
     * @param string            $link
     * @param string            $priority
     * @param array             $parameters
     */
    public function __construct(
        DateTimeInterface $createdAt,
        $icon,
        $category,
        $description,
        $link,
        $priority,
        $parameters = []
    ) {
        $this->createdAt   = $createdAt;
        $this->category    = $category;
        $this->description = $description;
        $this->link        = $link;
        $this->priority    = $priority;
        $this->parameters  = $parameters;
        $this->icon        = $icon;
    }
}
