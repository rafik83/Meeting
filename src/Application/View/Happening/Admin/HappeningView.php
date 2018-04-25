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

    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $categoryTitle;

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var bool */
    public $questionAllowed;

    /** @var int|null */
    public $limit;

    /** @var int */
    public $participations;

    /** @var SpeakerView[] */
    public $speakers;

    /** @var bool */
    public $isPrivate;

    /** @var bool */
    public $hasProducts;

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
     * @param bool               $isPrivate
     * @param bool               $hasProducts
     */
    public function __construct(
        int $id,
        string $title,
        string $categoryTitle,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        bool $questionAllowed,
        ?int $limit = null,
        int $participations,
        array $speakers = [],
        bool $isPrivate,
        bool $hasProducts = false
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
        $this->isPrivate       = $isPrivate;
        $this->hasProducts     = $hasProducts;
    }

    /**
     * @return bool
     */
    public function hasLimit()
    {
        return $this->limit !== null;
    }
}
