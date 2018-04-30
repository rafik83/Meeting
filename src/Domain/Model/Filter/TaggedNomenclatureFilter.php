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
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var array
     */
    private $nomenclaturesId = [];

    /**
     * @param Event  $event
     * @param string $tag
     * @param array  $nomenclaturesId
     */
    public function __construct(Event $event, $tag, array $nomenclaturesId)
    {
        $this->event           = $event;
        $this->tag             = $tag;
        $this->nomenclaturesId = $nomenclaturesId;
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
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return array
     */
    public function getNomenclaturesId()
    {
        return $this->nomenclaturesId;
    }
}
