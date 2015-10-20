<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class Update
{
    public $id;

    public $data;

    public function __construct($id, $data)
    {
        $this->id   = $id;
        $this->data = $data;
    }
}
