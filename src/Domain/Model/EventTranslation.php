<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
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
    private $iban;

    /**
     * @var string
     */
    private $billingAddress;

    /**
     * @var string
     */
    private $paymentCondition;

    /**
     * @var string
     */
    private $footer;

    /**
     * @param Event       $event
     * @param string      $locale
     * @param string      $description
     * @param string|null $iban
     * @param string|null $billingAddress
     * @param string|null $paymentCondition
     * @param string|null $footer
     */
    public function __construct(Event $event, $locale, $description, $iban = null, $billingAddress = null, $paymentCondition = null, $footer = null)
    {
        $this->event            = $event;
        $this->locale           = $locale;
        $this->description      = $description;
        $this->iban             = $iban;
        $this->billingAddress   = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->footer           = $footer;
    }

    /**
     * @param string $iban
     * @param string $billingAddress
     * @param string $paymentCondition
     * @param string $footer
     */
    public function setBillingConfiguration($iban, $billingAddress, $paymentCondition, $footer)
    {
        $this->iban             = $iban;
        $this->billingAddress   = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->footer           = $footer;
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
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     *
     * @return EventTranslation
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

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
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param string $footer
     *
     * @return EventTranslation
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;

        return $this;
    }

}
