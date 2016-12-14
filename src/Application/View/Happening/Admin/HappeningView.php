<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening\Admin;

class HappeningView
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
    public $categoryTitle;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var bool
     */
    public $questionAllowed;

    /**
     * @var int|null
     */
    public $limit;

    /**
     * @var int
     */
    public $participations;

    /**
     * @var SpeakerView[]
     */
    public $speakers;

    /**
     * @param int                $id
     * @param string             $title
     * @param string             $categoryTitle
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $questionAllowed
     * @param int|null           $limit
     * @param int                $participations
     * @param SpeakerView[]      $speakers
     */
    public function __construct(
        $id,
        $title,
        $categoryTitle,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $questionAllowed,
        $limit = null,
        $participations,
        array $speakers = []
    ) {
        $this->id              = $id;
        $this->title           = $title;
        $this->categoryTitle   = $categoryTitle;
        $this->begin           = $begin;
        $this->end             = $end;
        $this->questionAllowed = $questionAllowed;
        $this->limit           = $limit;
        $this->participations  = $participations;
        $this->speakers        = $speakers;
    }

    /**
     * @return bool
     */
    public function hasLimit()
    {
        return $this->limit !== null;
    }
}
