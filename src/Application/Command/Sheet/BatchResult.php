<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

class BatchResult
{
    /**
     * @var int
     */
    public $count;

    /**
     * BatchResult constructor.
     *
     * @param int $count
     */
    public function __construct($count)
    {
        $this->count = $count;
    }
}
