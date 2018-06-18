<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Event;

use Proximum\Vimeet\Domain\Payment\Mode;

class Configuration
{
    /** @var string */
    private $headerLeftColor;

    /** @var string */
    private $headerRightColor;

    /** @var string */
    private $leftColor;

    /** @var string */
    private $rightColor;

    /** @var string */
    private $textColor;

    /** @var int In Minutes */
    private $scheduleScale = 30;

    /** @var string */
    private $legalInfo;

    /** @var string */
    private $contactLastName;

    /** @var string */
    private $contactFirstName;

    /** @var string */
    private $organiserPhone;

    /** @var string */
    private $organiserWebsite;

    /** @var bool */
    private $allowDeposit = false;

    /** @var \DateTimeInterface */
    private $depositUntil;

    /** @var float */
    private $minimumForDeposit;

    /** @var int */
    private $deposit;

    /** @var \DateTimeInterface|null "la date de mise en ligne du catalogue" */
    private $catalogOnlineDate;

    /** @var \DateTimeInterface|null "la date d'ouverture des inscriptions au s-event" */
    private $happeningsOpenDate;

    /** @var \DateTimeInterface|null "la date de publication des agendas définitifs (RDV)" */
    private $schedulePublishDate;

    /** @var null|\DateTimeInterface "la date d'activation des notifications SMS" */
    private $smsActivationDate;

    /** @var bool */
    private $meetingRequestUpdateLocked;

    /** @var array */
    private $paymentModes;

    /** @var string */
    private $analyticsCode;

    /** @var null|string */
    private $backgroundImage;

    /** @var string */
    private $backgroundColor;

    /**
     * "Bloquer la demande de rendez-vous"
     *
     * Date after which users can not request a meeting with other users on this event
     *
     * @var \DateTimeInterface|null
     */
    private $closeMeetingRequestDate;

    /**
     * "Bloquer les acceptations/refus des RDV"
     *
     * Date after which the state of the meeting requests can not be changed
     *
     * @var \DateTimeInterface|null
     */
    private $closeAnsweringMeetingRequestDate;

    /** @var \DateTimeInterface|null Date d'ouverture de l'agenda */
    private $agendaOnlineDate;

    /** @var \DateTimeInterface|null "Date d'ouverture des inscriptions" */
    private $registrationOpenDate;

    /** @var \DateTimeInterface|null "Date de cloture des inscriptions" */
    private $registrationCloseDate;

    public function __construct()
    {
        $this->meetingRequestUpdateLocked = false;
        $this->paymentModes = Mode::getPaymentModes();

        $this->rightColor = '#000000';
        $this->leftColor = '#000000';
        $this->headerRightColor = '#000000';
        $this->headerLeftColor = '#000000';
        $this->textColor = '#FFFFFF';
        $this->backgroundColor = '#FFFFFF';
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
     * @param array                   $paymentModes
     * @param bool                    $allowDeposit
     * @param \DateTimeInterface|null $depositUntil
     * @param float|null              $minimumForDeposit
     * @param int|null                $deposit
     */
    public function updatePaymentConditions(
        array $paymentModes,
        $allowDeposit,
        \DateTimeInterface $depositUntil = null,
        $minimumForDeposit = null,
        $deposit = null
    ) {
        $this->paymentModes      = $paymentModes;
        $this->allowDeposit      = $allowDeposit;
        $this->depositUntil      = $depositUntil;
        $this->minimumForDeposit = $minimumForDeposit;
        $this->deposit           = $deposit;
    }

    /**
     * @param string $leftColor
     * @param string $rightColor
     * @param string $textColor
     * @param string $headerLeftColor
     * @param string $headerRightColor
     * @param string $backgroundColor
     */
    public function setColors(
        string $leftColor,
        string $rightColor,
        string $textColor,
        string $headerLeftColor,
        string $headerRightColor,
        string $backgroundColor
    ) {
        $this->leftColor  = $leftColor;
        $this->rightColor = $rightColor;
        $this->textColor  = $textColor;
        $this->headerLeftColor = $headerLeftColor;
        $this->headerRightColor = $headerRightColor;
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @return string
     */
    public function getHeaderLeftColor(): string
    {
        return $this->headerLeftColor;
    }

    /**
     * @return string
     */
    public function getHeaderRightColor(): string
    {
        return $this->headerRightColor;
    }


    /**
     * @return string
     */
    public function getLeftColor(): string
    {
        return $this->leftColor;
    }

    /**
     * @return string
     */
    public function getRightColor(): string
    {
        return $this->rightColor;
    }

    /**
     * @return string
     */
    public function getTextColor(): string
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
     * @param \DateTimeInterface|null $catalogOnlineDate
     * @param \DateTimeInterface|null $happeningsOpenDate
     * @param \DateTimeInterface|null $schedulePublishDate
     * @param \DateTimeInterface|null $closeMeetingRequestDate
     * @param \DateTimeInterface|null $closeAnsweringMeetingRequestDate
     * @param \DateTimeInterface|null $smsActivationDate
     * @param \DateTimeInterface|null $agendaOnlineDate
     * @param \DateTimeInterface|null $registrationOpenDate
     * @param \DateTimeInterface|null $registrationCloseDate
     *
     * @return Configuration
     */
    public function setDates(
        \DateTimeInterface $catalogOnlineDate = null,
        \DateTimeInterface $happeningsOpenDate = null,
        \DateTimeInterface $schedulePublishDate = null,
        \DateTimeInterface $closeMeetingRequestDate = null,
        \DateTimeInterface $closeAnsweringMeetingRequestDate = null,
        \DateTimeInterface $smsActivationDate = null,
        \DateTimeInterface $agendaOnlineDate = null,
        \DateTimeInterface $registrationOpenDate = null,
        \DateTimeInterface $registrationCloseDate = null
    ) {
        $this->catalogOnlineDate                = $catalogOnlineDate;
        $this->happeningsOpenDate               = $happeningsOpenDate;
        $this->schedulePublishDate              = $schedulePublishDate;
        $this->closeMeetingRequestDate          = $closeMeetingRequestDate;
        $this->closeAnsweringMeetingRequestDate = $closeAnsweringMeetingRequestDate;
        $this->smsActivationDate                = $smsActivationDate;
        $this->agendaOnlineDate                 = $agendaOnlineDate;
        $this->registrationOpenDate             = $registrationOpenDate;
        $this->registrationCloseDate            = $registrationCloseDate;

        return $this;
    }

    /**
     * Get catalogOnlineDate
     *
     * @return \DateTimeInterface|null
     */
    public function getCatalogOnlineDate()
    {
        return $this->catalogOnlineDate;
    }

    /**
     * Get happeningsOpenDate
     *
     * @return \DateTimeInterface|null
     */
    public function getHappeningsOpenDate()
    {
        return $this->happeningsOpenDate;
    }

    /**
     * Get schedulePublishDate
     *
     * @return \DateTimeInterface|null
     */
    public function getSchedulePublishDate()
    {
        return $this->schedulePublishDate;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCloseMeetingRequestDate()
    {
        return $this->closeMeetingRequestDate;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCloseAnsweringMeetingRequestDate()
    {
        return $this->closeAnsweringMeetingRequestDate;
    }

    /**
     * @return null|string
     */
    public function getAnalyticsCode()
    {
        return $this->analyticsCode;
    }

    /**
     * @param null|string $analyticsCode
     */
    public function setAnalyticsCode($analyticsCode)
    {
        $this->analyticsCode = $analyticsCode;
    }

    /**
     * @return bool
     */
    public function isMeetingRequestUpdateLocked()
    {
        return $this->meetingRequestUpdateLocked;
    }

    /**
     * @param bool $meetingRequestUpdateLocked
     */
    public function setMeetingRequestUpdateLocked($meetingRequestUpdateLocked)
    {
        $this->meetingRequestUpdateLocked = $meetingRequestUpdateLocked;
    }

    /**
     * @return array
     */
    public function getPaymentModes()
    {
        return $this->paymentModes;
    }

    /**
     * @return bool
     */
    public function isAllowedToPayRemaining()
    {
        return in_array(Mode::PAYMENT_PAYPAL, $this->paymentModes, true);
    }

    /**
     * @return null|\DateTimeInterface
     */
    public function getSmsActivationDate()
    {
        return $this->smsActivationDate;
    }

    /**
     * @return null|\DateTimeInterface
     */
    public function getAgendaOnlineDate()
    {
        return $this->agendaOnlineDate;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getRegistrationOpenDate(): ?\DateTimeInterface
    {
        return $this->registrationOpenDate;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getRegistrationCloseDate(): ?\DateTimeInterface
    {
        return $this->registrationCloseDate;
    }

    /**
     * @return null|string
     */
    public function getBackgroundImage(): ?string
    {
        return $this->backgroundImage;
    }

    /**
     * @param null|string $backgroundImage
     */
    public function setBackgroundImage(?string $backgroundImage): void
    {
        $this->backgroundImage = $backgroundImage;
    }

    /**
     * @return string
     */
    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    /**
     * @return bool
     */
    public function hasBackgroundImage(): bool
    {
        return null !== $this->backgroundImage;
    }
}
