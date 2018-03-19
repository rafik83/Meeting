<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class AvailabilityTimeRange
{
    /** @var null|int */
    private $id;

    /** @var Event */
    private $event;

    /**
     * Libellé backoffice
     *
     * @var string
     */
    private $name;

    /** @var \DateTimeInterface */
    private $begin;

    /** @var \DateTimeInterface */
    private $end;

    public function __construct(Event $event, string $name, \DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->event = $event;
        $this->name = $name;
        $this->begin = $begin;
        $this->end = $end;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBegin(): \DateTimeInterface
    {
        return $this->begin;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }
}
