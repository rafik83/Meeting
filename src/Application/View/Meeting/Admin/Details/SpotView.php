<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Admin\Details;

class SpotView
{
    /**
     * @var string
     */
    public $ref;

    /**
     * @param $ref
     */
    public function __construct($ref)
    {
        $this->ref = $ref;
    }
}
