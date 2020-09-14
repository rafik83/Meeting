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
    private $headerButtonLeftColor;

    /** @var string */
    private $headerButtonRightColor;

    /** @var string */
    private $headerButtonTextColor;

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

    /** @var null|\DateTimeInterface "la date d'activation du button de test visio" */
    private $enableVisioTestMenuButtonDate;

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

    /** @var \DateTimeInterface|null "Active l'affichage du badge pour le participant" */
    private $enableBadgeForParticipantDate;

    /** @var bool */
    private $displayParticipantNameOnPlanning = false;

    /** @var bool */
    private $displayParticipantPositionOnPlanning = false;

    /** @var bool */
    private $visio = false;

    /** @var \DateTimeInterface|null "Date d'ouverture du networking" */
    private $networkingOpenDate;

    /** @var \DateTimeInterface|null "Date de cloture du networking" */
    private $networkingCloseDate;

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

        $this->headerButtonLeftColor = '#2F2F2F';
        $this->headerButtonRightColor = '#2F2F2F';
        $this->headerButtonTextColor = '#FFFFFF';
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
     * @param string $headerButtonLeftColor
     * @param string $headerButtonRightColor
     * @param string $headerButtonTextColor
     */
    public function setColors(
        string $leftColor,
        string $rightColor,
        string $textColor,
        string $headerLeftColor,
        string $headerRightColor,
        string $backgroundColor,
        string $headerButtonLeftColor,
        string $headerButtonRightColor,
        string $headerButtonTextColor
    ): void {
        $this->leftColor  = $leftColor;
        $this->rightColor = $rightColor;
        $this->textColor  = $textColor;
        $this->headerLeftColor = $headerLeftColor;
        $this->headerRightColor = $headerRightColor;
        $this->backgroundColor = $backgroundColor;
        $this->headerButtonLeftColor = $headerButtonLeftColor;
        $this->headerButtonRightColor = $headerButtonRightColor;
        $this->headerButtonTextColor = $headerButtonTextColor;
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

    public function getHeaderButtonLeftColor(): string
    {
        return $this->headerButtonLeftColor;
    }

    public function getHeaderButtonRightColor(): string
    {
        return $this->headerButtonRightColor;
    }

    public function getHeaderButtonTextColor(): string
    {
        return $this->headerButtonTextColor;
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

    public function setDates(
        \DateTimeInterface $catalogOnlineDate = null,
        \DateTimeInterface $happeningsOpenDate = null,
        \DateTimeInterface $schedulePublishDate = null,
        \DateTimeInterface $closeMeetingRequestDate = null,
        \DateTimeInterface $closeAnsweringMeetingRequestDate = null,
        \DateTimeInterface $smsActivationDate = null,
        \DateTimeInterface $agendaOnlineDate = null,
        \DateTimeInterface $registrationOpenDate = null,
        \DateTimeInterface $registrationCloseDate = null,
        \DateTimeInterface $enableBadgeForParticipantDate = null,
        \DateTimeInterface $enableVisioTestMenuButtonDate = null,
        \DateTimeInterface $networkingOpenDate = null,
        \DateTimeInterface $networkingCloseDate = null
    ): self {
        $this->catalogOnlineDate = $catalogOnlineDate;
        $this->happeningsOpenDate = $happeningsOpenDate;
        $this->schedulePublishDate = $schedulePublishDate;
        $this->closeMeetingRequestDate = $closeMeetingRequestDate;
        $this->closeAnsweringMeetingRequestDate = $closeAnsweringMeetingRequestDate;
        $this->smsActivationDate = $smsActivationDate;
        $this->agendaOnlineDate = $agendaOnlineDate;
        $this->registrationOpenDate = $registrationOpenDate;
        $this->registrationCloseDate = $registrationCloseDate;
        $this->enableBadgeForParticipantDate = $enableBadgeForParticipantDate;
        $this->enableVisioTestMenuButtonDate = $enableVisioTestMenuButtonDate;
        $this->networkingOpenDate = $networkingOpenDate;
        $this->networkingCloseDate = $networkingCloseDate;

        return $this;
    }

    public function getCatalogOnlineDate(): ?\DateTimeInterface
    {
        return $this->catalogOnlineDate;
    }

    public function getHappeningsOpenDate(): ?\DateTimeInterface
    {
        return $this->happeningsOpenDate;
    }

    public function getSchedulePublishDate(): ?\DateTimeInterface
    {
        return $this->schedulePublishDate;
    }

    public function getCloseMeetingRequestDate(): ?\DateTimeInterface
    {
        return $this->closeMeetingRequestDate;
    }

    public function getCloseAnsweringMeetingRequestDate(): ?\DateTimeInterface
    {
        return $this->closeAnsweringMeetingRequestDate;
    }

    public function getEnableVisioTestMenuButtonDate(): ?\DateTimeInterface
    {
        return $this->enableVisioTestMenuButtonDate;
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

    public function getEnableBadgeForParticipantDate(): ?\DateTimeInterface
    {
        return $this->enableBadgeForParticipantDate;
    }

    public function isVisio(): bool
    {
        return $this->visio;
    }

    public function setVisio(bool $visio): void
    {
        $this->visio = $visio;
    }

    /**
     * @return bool
     */
    public function displayParticipantNameOnPlanning(): bool
    {
        return $this->displayParticipantNameOnPlanning;
    }

    /**
     * @return bool
     */
    public function displayParticipantPositionOnPlanning(): bool
    {
        return $this->displayParticipantPositionOnPlanning;
    }

    /**
     * @param bool $displayParticipantNameOnPlanning
     * @param bool $displayParticipantPositionOnPlanning
     */
    public function setParticipantInfoToDisplayOnPlanning(
        bool $displayParticipantNameOnPlanning,
        bool $displayParticipantPositionOnPlanning
    ): void {
        $this->displayParticipantNameOnPlanning = $displayParticipantNameOnPlanning;
        $this->displayParticipantPositionOnPlanning = $displayParticipantPositionOnPlanning;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getNetworkingOpenDate(): ?\DateTimeInterface
    {
        return $this->networkingOpenDate;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getNetworkingCloseDate(): ?\DateTimeInterface
    {
        return $this->networkingCloseDate;
    }
}
