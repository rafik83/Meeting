<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class EventListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $domain;

    /**
     * @param int    $id
     * @param string $title
     * @param string $domain
     */
    public function __construct($id, $title, $domain)
    {
        $this->id     = $id;
        $this->title  = $title;
        $this->domain = $domain;
    }
}
