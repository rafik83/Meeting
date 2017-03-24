<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip;

class TipViewQuery
{
    /** @var int */
    public $page;
    
    /**
     * TipViewQuery constructor.
     *
     * @param int $page
     */
    public function __construct($page)
    {
        $this->page = $page;
    }
}
