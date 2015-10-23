<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class ParticipantView
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $data;

    /**
     * @var boolean
     */
    public $owner;

    /**
     * @var string
     */
    public $userEmail;

    /**
     * @var integer
     */
    public $eventId;

    /**
     * @var string
     */
    public $eventTitle;

    /**
     * @var integer
     */
    public $typeId;

    /**
     * @var string
     */
    public $typeTitle;

    /**
     * @param integer $id
     * @param boolean $owner
     * @param string  $data
     * @param string  $userEmail
     * @param integer $eventId
     * @param string  $eventTitle
     * @param integer $typeId
     * @param string  $typeTitle
     */
    public function __construct($id, $owner, $data, $userEmail, $eventId, $eventTitle, $typeId, $typeTitle)
    {
        $data = json_decode($data, true);

        if ($data === null) {
            throw new \InvalidArgumentException('Invalid json data');
        }

        $this->id         = $id;
        $this->owner      = $owner;
        $this->data       = $data;
        $this->userEmail  = $userEmail;
        $this->eventId    = $eventId;
        $this->eventTitle = $eventTitle;
        $this->typeId     = $typeId;
        $this->typeTitle  = $typeTitle;
    }
}
