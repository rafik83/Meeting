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
    private $footers;

    /**
     * @var string
     */
    private $legalInfo;

    /**
     * @param string $leftColor
     * @param string $rightColor
     * @param string $textColor
     */
    public function __construct($leftColor, $rightColor, $textColor)
    {
        $this->leftColor = $leftColor;
        $this->rightColor = $rightColor;
        $this->textColor = $textColor;
    }

    /**
     * @param string $leftColor
     * @param string $rightColor
     * @param string $textColor
     */
    public function setColors($leftColor, $rightColor, $textColor)
    {
        $this->leftColor = $leftColor;
        $this->rightColor = $rightColor;
        $this->textColor = $textColor;
    }

    /**
     * @param string $iban
     * @param string $billingAddress
     * @param string $paymentCondition
     * @param string $footers
     * @param string $legalInfo
     *
     * @return Configuration
     */
    public function setBillingConfiguration($iban, $billingAddress, $paymentCondition, $footers, $legalInfo)
    {
        $this->iban             = $iban;
        $this->billingAddress   = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->footers          = $footers;
        $this->legalInfo        = $legalInfo;

        return $this;
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
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     *
     * @return Configuration
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
     * @return Configuration
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
     * @return Configuration
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
     * @return Configuration
     */
    public function setFooters($footers)
    {
        $this->footers = $footers;

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

}
