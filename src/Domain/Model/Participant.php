<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\Participant\Type;

class Participant
{
    private $id;

    private $user;

    private $event;

    private $type;

    private $data;

    public function __construct(User $user, Event $event, Type $type, $data)
    {
        $this->user  = $user;
        $this->event = $event;
        $this->type  = $type;
        $this->data  = $data;
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
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get event
     *
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
