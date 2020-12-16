<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var array
     */
    public $parameters;

    /**
     * NotificationView constructor.
     *
     * @param DateTimeInterface $createdAt
     * @param string            $icon
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
        array $parameters = []
    ) {
        $this->createdAt   = $createdAt;
        $this->category    = $category;
        $this->description = $description;
        $this->link        = $link;
        $this->priority    = $priority;
        $this->icon        = $icon;
        $this->parameters  = $parameters;
    }

    /**
     * @return bool
     */
    public function hasPriorityLabel()
    {
        return Notification::PRIORITY_NONE !== $this->priority;
    }

    /**
     * @return bool
     */
    public function isImportant()
    {
        return Notification::PRIORITY_IMPORTANT === $this->priority;
    }

    public function isRequired(): bool
    {
        return Notification::PRIORITY_REQUIRED === $this->priority;
    }
}
