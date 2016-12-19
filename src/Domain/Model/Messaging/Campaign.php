<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Messaging;

use Proximum\Vimeet\Domain\Model\Event;

class Campaign
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $title;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * @param Event              $event
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, $title, \DateTimeInterface $createdAt)
    {
        $this->event     = $event;
        $this->title     = $title;
        $this->createdAt = $createdAt;
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
