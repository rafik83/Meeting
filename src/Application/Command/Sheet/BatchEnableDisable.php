<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

class BatchEnableDisable
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var bool
     */
    public $state;

    /**
     * BatchValidate constructor.
     *
     * @param array $ids
     * @param bool  $state
     */
    public function __construct(array $ids, $state)
    {
        $this->ids   = $ids;
        $this->state = $state;
    }
}
