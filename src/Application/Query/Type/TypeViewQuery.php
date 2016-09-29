<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Model\Event;

class TypeViewQuery
{
    /**
     * @var int
     */
    public $page;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * TypeViewQuery constructor.
     *
     * @param int    $page
     * @param Event  $event
     * @param string $locale
     */
    public function __construct($page, $event, $locale)
    {
        $this->page   = $page;
        $this->event  = $event;
        $this->locale = $locale;
    }
}
