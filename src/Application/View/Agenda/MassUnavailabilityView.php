<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class MassUnavailabilityView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $picto;

    /**
     * @var string
     */
    public $code;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $leftColor;

    /**
     * @var string
     */
    public $rightColor;

    /**
     * @param int                $id
     * @param string             $code
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $title
     * @param string             $description
     * @param string             $picto
     * @param string             $leftColor
     * @param string             $rightColor
     */
    public function __construct(
        $id,
        $code,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $title,
        $description,
        $picto,
        $leftColor,
        $rightColor
    ) {
        $this->id          = $id;
        $this->code        = $code;
        $this->begin       = $begin;
        $this->end         = $end;
        $this->title       = $title;
        $this->description = $description;
        $this->picto       = $picto;
        $this->leftColor   = $leftColor;
        $this->rightColor  = $rightColor;
    }

    /**
     * @return \DateInterval
     */
    public function getDuration()
    {
        return $this->end->diff($this->begin);
    }
}
