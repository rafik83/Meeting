<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Event;

class Configuration
{
    /**
     * @var string
     */
    private $leftColor;

    /**
     * @var string
     */
    private $rightColor;

    /**
     * @var string
     */
    private $textColor;

    /**
     * @var int
     */
    private $scheduleScale = 30;

    /**
     * "la date de mise en ligne du catalogue"
     *
     * @var \DateTimeInterface
     */
    private $catalogOnlineDate;

    /**
     * "la date d'ouverture des inscriptions au s-event"
     *
     * @var \DateTimeInterface
     */
    private $happeningsOpenDate;

    /**
     * "la date de publication des agendas définitifs (RDV)"
     *
     * @var \DateTimeInterface
     */
    private $schedulePublishDate;

    /**
     * @param string $leftColor
     * @param string $rightColor
     * @param string $textColor
     */
    public function __construct($leftColor, $rightColor, $textColor)
    {
        $this->leftColor  = $leftColor;
        $this->rightColor = $rightColor;
        $this->textColor  = $textColor;
    }

    /**
     * @param string $leftColor
     * @param string $rightColor
     * @param string $textColor
     */
    public function setColors($leftColor, $rightColor, $textColor)
    {
        $this->leftColor  = $leftColor;
        $this->rightColor = $rightColor;
        $this->textColor  = $textColor;
    }

    /**
     * @return string
     */
    public function getLeftColor()
    {
        return $this->leftColor;
    }

    /**
     * @return string
     */
    public function getRightColor()
    {
        return $this->rightColor;
    }

    /**
     * @return string
     */
    public function getTextColor()
    {
        return $this->textColor;
    }

    /**
     * Get scheduleScale
     *
     * @return int
     */
    public function getScheduleScale()
    {
        return $this->scheduleScale;
    }

    /**
     * Set scheduleScale
     *
     * @param int $scheduleScale
     *
     * @return Configuration
     */
    public function setScheduleScale($scheduleScale)
    {
        $this->scheduleScale = $scheduleScale;

        return $this;
    }

    /**
     * @param \DateTimeInterface $catalogOnlineDate
     * @param \DateTimeInterface $happeningsOpenDate
     * @param \DateTimeInterface $schedulePublishDate
     *
     * @return Configuration
     */
    public function setDates(
        \DateTimeInterface $catalogOnlineDate = null,
        \DateTimeInterface $happeningsOpenDate = null,
        \DateTimeInterface $schedulePublishDate = null
    ) {
        $this->catalogOnlineDate   = $catalogOnlineDate;
        $this->happeningsOpenDate  = $happeningsOpenDate;
        $this->schedulePublishDate = $schedulePublishDate;

        return $this;
    }

    /**
     * Get catalogOnlineDate
     *
     * @return \DateTimeInterface
     */
    public function getCatalogOnlineDate()
    {
        return $this->catalogOnlineDate;
    }

    /**
     * Get happeningsOpenDate
     *
     * @return \DateTimeInterface
     */
    public function getHappeningsOpenDate()
    {
        return $this->happeningsOpenDate;
    }

    /**
     * Get schedulePublishDate
     *
     * @return \DateTimeInterface
     */
    public function getSchedulePublishDate()
    {
        return $this->schedulePublishDate;
    }
}
