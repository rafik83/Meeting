<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

class BatchAccept
{
    /**
     * @var array
     */
    public $ids;

    /**
     * BatchAccept constructor.
     *
     * @param array $ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }
}
