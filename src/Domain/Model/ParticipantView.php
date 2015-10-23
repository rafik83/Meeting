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
     * @var string
     */
    public $userEmail;

    /**
     * @var boolean
     */
    public $owner;

    /**
     * @param integer $id
     * @param string  $data
     * @param string  $userEmail
     * @param boolean $owner
     */
    public function __construct($id, $data, $userEmail, $owner)
    {
        $data = json_decode($data, true);

        if ($data === null) {
            throw new \InvalidArgumentException('Invalid json data');
        }

        $this->id         = $id;
        $this->data       = $data;
        $this->userEmail  = $userEmail;
        $this->owner      = $owner;
    }
}
