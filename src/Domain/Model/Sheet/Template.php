<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Template
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $value;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Type[]
     */
    private $types;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * Template constructor.
     *
     * @param string            $title
     * @param string            $value
     * @param DateTimeInterface $createdAt
     */
    public function __construct($title, $value, DateTimeInterface $createdAt)
    {
        $this->title     = $title;
        $this->value     = $value;
        $this->createdAt = $createdAt;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Template
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string            $title
     * @param DateTimeInterface $createdAt
     *
     * @return Template
     */
    public function duplicate($title, DateTimeInterface $createdAt)
    {
        return new $this($title, $this->value, $createdAt);
    }
}
