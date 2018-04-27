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
        $locale,
        $description,
        $bankInfo = null,
        $billingAddress = null,
        $paymentCondition = null,
        $paymentFooter = null
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
     * @param string $bankInfo
     * @param string $billingAddress
     * @param string $paymentCondition
     * @param string $paymentFooter
     */
    public function setBillingConfiguration($bankInfo, $billingAddress, $paymentCondition, $paymentFooter)
    {
        $this->bankInfo         = $bankInfo;
        $this->billingAddress   = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->paymentFooter    = $paymentFooter;
    }

    /**
     * @return Event
     */
    public function getEvent()
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
}
