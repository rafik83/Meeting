<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class EventTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $bankInfo;

    /**
     * @var string
     */
    private $billingAddress;

    /**
     * @var string
     */
    private $paymentCondition;

    /**
     * Address, Siret, Capital ...
     *
     * @var string
     */
    private $paymentFooter;

    /** @var string|null */
    private $logo;

    /** @var string|null */
    private $logoExtension;

    /** @var string|null */
    private $mobileLogo;

    /** @var string|null */
    private $mobileLogoExtension;

    /** @var string|null */
    private $notificationImage;

    /** @var string|null */
    private $notificationImageExtension;

    /**
     * @param Event       $event
     * @param string      $locale
     * @param string      $description
     * @param string|null $bankInfo
     * @param string|null $billingAddress
     * @param string|null $paymentCondition
     * @param string|null $paymentFooter
     */
    public function __construct(
        Event $event,
        string $locale,
        string $description,
        ?string $bankInfo = null,
        ?string $billingAddress = null,
        ?string $paymentCondition = null,
        ?string $paymentFooter = null
    ) {
        $this->event            = $event;
        $this->locale           = $locale;
        $this->description      = $description;
        $this->bankInfo         = $bankInfo;
        $this->billingAddress   = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->paymentFooter    = $paymentFooter;
    }

    /**
     * @param string|null $bankInfo
     * @param string|null $billingAddress
     * @param string|null $paymentCondition
     * @param string|null $paymentFooter
     */
    public function setBillingConfiguration(
        ?string $bankInfo = null,
        ?string $billingAddress = null,
        ?string $paymentCondition = null,
        ?string $paymentFooter = null
    ): void {
        $this->bankInfo         = $bankInfo;
        $this->billingAddress   = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->paymentFooter    = $paymentFooter;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     *
     * @return EventTranslation
     */
    public function update($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankInfo()
    {
        return $this->bankInfo;
    }

    /**
     * @param string $bankInfo
     *
     * @return EventTranslation
     */
    public function setBankInfo($bankInfo)
    {
        $this->bankInfo = $bankInfo;

        return $this;
    }

    /**
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param string $billingAddress
     *
     * @return EventTranslation
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentCondition()
    {
        return $this->paymentCondition;
    }

    /**
     * @param string $paymentCondition
     *
     * @return EventTranslation
     */
    public function setPaymentCondition($paymentCondition)
    {
        $this->paymentCondition = $paymentCondition;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentFooter()
    {
        return $this->paymentFooter;
    }

    /**
     * @param string $paymentFooter
     *
     * @return EventTranslation
     */
    public function setPaymentFooter($paymentFooter)
    {
        $this->paymentFooter = $paymentFooter;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @return null|string
     */
    public function getLogoExtension(): ?string
    {
        return $this->logoExtension;
    }

    /**
     * @return null|string
     */
    public function getMobileLogo(): ?string
    {
        return $this->mobileLogo;
    }

    /**
     * @return null|string
     */
    public function getMobileLogoExtension(): ?string
    {
        return $this->mobileLogoExtension;
    }

    /**
     * @return null|string
     */
    public function getNotificationImage(): ?string
    {
        return $this->notificationImage;
    }

    /**
     * @return null|string
     */
    public function getNotificationImageExtension(): ?string
    {
        return $this->notificationImageExtension;
    }

    public function updateLogoAndMobileLogo(
        ?string $logo = null,
        ?string $logoExtension = null,
        ?string $mobileLogo = null,
        ?string $mobileLogoExtension = null,
        ?string $notificationImage = null,
        ?string $notificationImageExtension = null
    ): void {
        $this->logo = $logo;
        $this->logoExtension = $logoExtension;
        $this->mobileLogo = $mobileLogo;
        $this->mobileLogoExtension = $mobileLogoExtension;
        $this->notificationImage = $notificationImage;
        $this->notificationImageExtension = $notificationImageExtension;
    }
}
