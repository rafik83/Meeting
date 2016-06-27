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
     * In Minutes
     *
     * @var int
     */
    private $scheduleScale = 30;

    /**
     * @var string
     */
    private $contactLastName;

    /**
     * @var string
     */
    private $contactFirstName;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $mail;

    /**
     * @var string
     */
    private $website;

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
     * @param string $contactFirstName
     * @param string $contactLastName
     * @param string $phone
     * @param string $website
     */
    public function updatePracticalInfo($contactFirstName, $contactLastName, $phone, $website)
    {
        $this->contactFirstName = $contactFirstName;
        $this->contactLastName  = $contactLastName;
        $this->phone            = $phone;
        $this->website          = $website;
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
     * @return string
     */
    public function getContactLastName()
    {
        return $this->contactLastName;
    }

    /**
     * @return string
     */
    public function getContactFirstName()
    {
        return $this->contactFirstName;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
