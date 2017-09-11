<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Event;

use Proximum\Vimeet\Domain\Model\Event;

class ExtraParameters
{
    /** @var Event */
    private $event;

    /** @var string */
    private $type;

    /** @var null|string */
    private $name;

    /** @var null|string */
    private $value;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var \DateTimeInterface */
    private $updatedAt;

    /**
     * @param Event              $event
     * @param string             $type
     * @param string|null        $name
     * @param string|null        $value
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Event $event,
        string $type,
        string $name = null,
        string $value = null,
        \DateTimeInterface $createdAt
    ) {
        $this->event     = $event;
        $this->type      = $type;
        $this->name      = $name;
        $this->value     = $value;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param string|null        $name
     * @param string|null        $value
     * @param \DateTimeInterface $updatedAt
     */
    public function update(string $name = null, string $value = null, \DateTimeInterface $updatedAt)
    {
        $this->name = $name;
        $this->value = $value;
        $this->updatedAt = $updatedAt;
    }
}
