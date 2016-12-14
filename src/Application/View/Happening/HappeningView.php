<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

use DateInterval;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;

class HappeningView
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTimeInterface
     */
    private $beginHour;

    /**
     * @var \DateTimeInterface
     */
    private $endHour;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string|null
     */
    private $picture;

    /**
     * @var HappeningSpeakerView[]
     */
    private $speakers;

    /**
     * @var int
     */
    private $code;

    /**
     * @var HappeningCategoryView
     */
    private $category;

    /**
     * If participants of the current sheet participate
     *
     * @var bool
     */
    private $hasParticipations = false;

    /**
     * If the current user participates
     *
     * @var bool
     */
    private $currentUserParticipate = false;

    /**
     * @var bool
     */
    public $isFull = false;

    /**
     * @var int|null
     */
    public $limitParticipant;

    /**
     * HappeningView constructor.
     *
     * @param int                   $id
     * @param int                   $code
     * @param HappeningCategoryView $category
     * @param \DateTimeInterface    $beginHour
     * @param \DateTimeInterface    $endHour
     * @param string                $title
     * @param string                $description
     * @param string|null           $picture
     * @param Speaker[]             $speakers
     * @param null|int              $limitParticipant
     * @param bool                  $isFull
     */
    public function __construct(
        $id,
        $code,
        HappeningCategoryView $category,
        \DateTimeInterface $beginHour,
        \DateTimeInterface $endHour,
        $title,
        $description,
        $picture,
        array $speakers,
        $limitParticipant = null,
        $isFull = false
    ) {
        $this->id               = $id;
        $this->category         = $category;
        $this->beginHour        = $beginHour;
        $this->endHour          = $endHour;
        $this->title            = $title;
        $this->description      = $description;
        $this->picture          = $picture;
        $this->speakers         = $speakers;
        $this->code             = $code;
        $this->isFull           = $isFull;
        $this->limitParticipant = $limitParticipant;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return DateInterval
     */
    public function getDuration()
    {
        return $this->endHour->diff($this->beginHour);
    }

    /**
     * @return HappeningCategoryView
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBeginHour()
    {
        return $this->beginHour;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEndHour()
    {
        return $this->endHour;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @return HappeningSpeakerView[]
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    /**
     * @return bool
     */
    public function hasPicture()
    {
        return $this->picture !== null;
    }

    /**
     * @param bool $currentUserParticipate
     */
    public function setCurrentUserParticipate($currentUserParticipate)
    {
        $this->currentUserParticipate = $currentUserParticipate;
    }

    /**
     * @param bool $hasParticipations
     */
    public function setHasParticipation($hasParticipations)
    {
        $this->hasParticipations = $hasParticipations;
    }

    /**
     * @return bool
     */
    public function doesCurrentUserParticipate()
    {
        return $this->currentUserParticipate;
    }

    /**
     * @return bool
     */
    public function hasParticipations()
    {
        return $this->hasParticipations;
    }

    /**
     * Set isFull to true
     */
    public function setIsFull()
    {
        $this->isFull = true;
    }
}
