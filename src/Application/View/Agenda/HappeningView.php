<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class HappeningView extends AbstractTimeEntityView
{
    /** @var int */
    public $id;

    /** @var string */
    public $picto;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var array */
    public $speakers;

    /** @var bool */
    public $isFull = false;

    /** @var int|null */
    public $limitParticipant;

    /** @var string */
    public $leftColor;

    /** @var string */
    public $rightColor;

    /** @var string */
    public $timeZone;

    /**
     * @param int                $id
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $title
     * @param string             $description
     * @param SpeakerView[]      $speakers
     * @param string             $picto
     * @param string             $leftColor
     * @param string             $rightColor
     * @param string             $timeZone
     * @param null|int           $limitParticipant
     */
    public function __construct(
        $id,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $title,
        $description,
        array $speakers,
        $picto,
        $leftColor,
        $rightColor,
        $timeZone,
        $limitParticipant = null
    ) {
        $this->id               = $id;
        $this->begin            = $begin;
        $this->end              = $end;
        $this->title            = $title;
        $this->description      = $description;
        $this->speakers         = $speakers;
        $this->picto            = $picto;
        $this->leftColor        = $leftColor;
        $this->rightColor       = $rightColor;
        $this->timeZone         = $timeZone;
        $this->limitParticipant = $limitParticipant;
    }

    /**
     * @return bool
     */
    public function hasParticipations(): bool
    {
        return true;
    }
}
