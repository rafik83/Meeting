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
    private $legalInfo;

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
    private $organiserPhone;
    
    /**
     * @var string
     */
    private $organiserWebsite;

    /**
     * @var bool
     */
    private $allowDeposit = false;

    /**
     * @var \DateTimeInterface
     */
    private $depositUntil;

    /**
     * @var float
     */
    private $minimumForDeposit;

    /**
     * @var int
     */
    private $deposit;

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
     * @param string $contactFirstName
     * @param string $contactLastName
     * @param string $organiserPhone
     * @param string $organiserWebsite
     */
    public function updatePracticalInfo($contactFirstName, $contactLastName, $organiserPhone, $organiserWebsite)
    {
        $this->contactFirstName = $contactFirstName;
        $this->contactLastName  = $contactLastName;
        $this->organiserPhone   = $organiserPhone;
        $this->organiserWebsite = $organiserWebsite;
    }

    /**
     * @return bool
     */
    public function isAllowDeposit()
    {
        return $this->allowDeposit;
    }

    /**
     * @return bool
     */
    public function isDepositAllowed()
    {
        return $this->allowDeposit;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDepositUntil()
    {
        return $this->depositUntil;
    }

    /**
     * @return float
     */
    public function getMinimumForDeposit()
    {
        return $this->minimumForDeposit;
    }

    /**
     * @return int
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @param bool                    $allowDeposit
     * @param \DateTimeInterface|null $depositUntil
     * @param float|null              $minimumForDeposit
     * @param int|null                $deposit
     */
    public function updatePaymentConditions(
        $allowDeposit,
        \DateTimeInterface $depositUntil = null,
        $minimumForDeposit = null,
        $deposit = null
    ) {
        $this->allowDeposit      = $allowDeposit;
        $this->depositUntil      = $depositUntil;
        $this->minimumForDeposit = $minimumForDeposit;
        $this->deposit           = $deposit;
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
    public function getLegalInfo()
    {
        return $this->legalInfo;
    }

    /**
     * @param string $legalInfo
     *
     * @return Configuration
     */
    public function setLegalInfo($legalInfo)
    {
        $this->legalInfo = $legalInfo;

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
    public function getOrganiserPhone()
    {
        return $this->organiserPhone;
    }
    
    /**
     * @return string
     */
    public function getOrganiserWebsite()
    {
        return $this->organiserWebsite;
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
