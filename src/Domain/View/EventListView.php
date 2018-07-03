<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

use Proximum\Vimeet\Application\View\Event\DayView;
use Proximum\Vimeet\Domain\Model\EventInterface;

class EventListView implements EventInterface
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
     * @var DayView[]
     */
    public $days;

    /** @var bool */
    public $visible;

    /** @var bool */
    public $visio;

    public function __construct(
        int $id,
        string $title,
        string $domain,
        array $locales,
        string $fallback,
        bool $visible,
        array $days = [],
        bool $visio = false
    ) {
        $this->id       = $id;
        $this->title    = $title;
        $this->domain   = $domain;
        $this->locales  = $locales;
        $this->fallback = $fallback;
        $this->visible  = $visible;
        $this->days     = $days;
        $this->visio    = $visio;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
