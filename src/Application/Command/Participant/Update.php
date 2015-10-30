<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

class Update
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    public $data;

    /**
     * @param int   $id
     * @param array $data
     */
    public function __construct($id, array $data)
    {
        $this->id   = $id;
        $this->data = $data;
    }
}
