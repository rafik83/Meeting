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
     * @var array
     */
    public $locales;

    /**
     * @var string
     */
    public $fallback;

    /**
     * @param int    $id
     * @param string $title
     * @param string $domain
     * @param array  $locales
     * @param string $fallback
     */
    public function __construct($id, $title, $domain, array $locales, $fallback)
    {
        $this->id       = $id;
        $this->title    = $title;
        $this->domain   = $domain;
        $this->locales  = $locales;
        $this->fallback = $fallback;
    }
}
