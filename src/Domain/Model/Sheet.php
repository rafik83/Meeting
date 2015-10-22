<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * "Fiche de participation"
 */
class Sheet
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var ArrayCollection
     */
    private $participants;

    /**
     * @var string
     */
    private $data;

    public function __construct(Event $event, Type $type)
    {
        $this->event        = $event;
        $this->type         = $type;
        $this->participants = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get type
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get participants
     *
     * @return mixed
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param mixed $data
     *
     * @return Sheet
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
