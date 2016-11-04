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
use Proximum\Vimeet\Domain\Notification\Notification;

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
     * @var string
     */
    public $locale;

    /**
     * NotificationView constructor.
     *
     * @param DateTimeInterface $createdAt
     * @param                   $icon
     * @param string            $category
     * @param string            $description
     * @param string            $link
     * @param string            $priority
     * @param string            $locale
     */
    public function __construct(
        DateTimeInterface $createdAt,
        $icon,
        $category,
        $description,
        $link,
        $priority,
        $locale
    ) {
        $this->createdAt   = $createdAt;
        $this->category    = $category;
        $this->description = $description;
        $this->link        = $link;
        $this->priority    = $priority;
        $this->icon        = $icon;
        $this->locale      = $locale;
    }

    /**
     * @return bool
     */
    public function hasLabel()
    {
        return $this->priority !== Notification::PRIORITY_NONE;
    }
}
