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
     * @var string
     */
    private $type;

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
     * @var string
     */
    private $picture;

    /**
     * @var Speaker[]
     */
    private $speakers;

    /**
     * HappeningView constructor.
     *
     * @param string             $type
     * @param \DateTimeInterface $beginHour
     * @param \DateTimeInterface $endHour
     * @param string             $title
     * @param string             $description
     * @param string             $picture
     * @param Speaker[]          $speakers
     */
    public function __construct(
        $type,
        \DateTimeInterface $beginHour,
        \DateTimeInterface $endHour,
        $title,
        $description,
        $picture,
        array $speakers
    ) {
        $this->type        = $type;
        $this->beginHour   = $beginHour;
        $this->endHour     = $endHour;
        $this->title       = $title;
        $this->description = $description;
        $this->picture     = $picture;
        $this->speakers    = $speakers;
    }

    /**
     * @return DateInterval
     */
    public function getDuration()
    {
        return $this->endHour->diff($this->beginHour);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @return Speaker[]
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }
}
