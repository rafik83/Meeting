<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

use Proximum\Vimeet\Application\View\Agenda\AbstractTimeEntityView;

class HappeningView extends AbstractTimeEntityView implements ProgramElementViewInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var string|null */
    private $picture;

    /** @var HappeningSpeakerView[] */
    private $speakers;

    /** @var HappeningCategoryView */
    private $category;

    /** @var bool If participants of the current sheet participate */
    private $hasParticipations = false;

    /** @var bool If the current user participates */
    private $currentUserParticipate = false;

    /** @var bool */
    private $isFull;

    /** @var int|null */
    public $limitParticipant;

    /** @var string */
    public $timeZone;
    /** @var bool */
    private $canAccessToWebinar;

    /**
     * HappeningView constructor.
     *
     * @param int                    $id
     * @param HappeningCategoryView  $category
     * @param \DateTimeInterface     $beginHour
     * @param \DateTimeInterface     $endHour
     * @param string                 $title
     * @param string                 $description
     * @param string|null            $picture
     * @param HappeningSpeakerView[] $speakers
     * @param string                 $timeZone
     * @param null|int               $limitParticipant
     * @param bool                   $isFull
     */
    public function __construct(
        $id,
        HappeningCategoryView $category,
        \DateTimeInterface $beginHour,
        \DateTimeInterface $endHour,
        $title,
        $description,
        $picture,
        array $speakers,
        $timeZone,
        $limitParticipant = null,
        $isFull = false,
        bool $canAccessToWebinar = false
    ) {
        $this->id = $id;
        $this->category = $category;
        $this->begin = $beginHour;
        $this->end = $endHour;
        $this->title = $title;
        $this->description = $description;
        $this->picture = $picture;
        $this->speakers = $speakers;
        $this->isFull = $isFull;
        $this->limitParticipant = $limitParticipant;
        $this->timeZone = $timeZone;
        $this->canAccessToWebinar = $canAccessToWebinar;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return HappeningCategoryView
     */
    public function getCategory()
    {
        return $this->category;
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
        return null !== $this->picture;
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
    public function setFull()
    {
        $this->isFull = true;
    }

    /**
     * @return bool
     */
    public function isFull()
    {
        return $this->isFull;
    }

    /**
     * @return string
     */
    public function getPicto()
    {
        return $this->getCategory()->getPicto();
    }

    /**
     * {@inheritdoc}
     */
    public function isHappeningView(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isMassUnavailabilityView(): bool
    {
        return false;
    }

    public function webinarOpened(): bool
    {
        return $this->canAccessToWebinar;
    }
}
