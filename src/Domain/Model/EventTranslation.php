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
    private $billingAddress;

    /**
     * @var string
     */
    private $paymentCondition;

    /**
     * @var string
     */
    private $footers;

    /**
     * @param Event  $event
     * @param string $locale
     * @param string $description
     */
    public function __construct(Event $event, $locale, $description)
    {
        $this->event       = $event;
        $this->locale      = $locale;
        $this->description = $description;
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
    public function getFooters()
    {
        return $this->footers;
    }

    /**
     * @param string $footers
     *
     * @return EventTranslation
     */
    public function setFooters($footers)
    {
        $this->footers = $footers;

        return $this;
    }

}
