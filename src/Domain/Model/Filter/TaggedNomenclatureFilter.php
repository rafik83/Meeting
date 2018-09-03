<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Filter;

use Proximum\Vimeet\Domain\Model\Event;

class TaggedNomenclatureFilter
{
    /** @var null|int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $tag;

    /** @var int[] */
    private $nomenclaturesId = [];

    /**
     * @param int[]  $nomenclaturesId
     */
    public function __construct(Event $event, string $tag, array $nomenclaturesId)
    {
        $this->event = $event;
        $this->tag = $tag;
        $this->nomenclaturesId = array_values($nomenclaturesId);
    }

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return int[]
     */
    public function getNomenclaturesId(): array
    {
        return $this->nomenclaturesId;
    }
}
