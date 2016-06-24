<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event\Configuration;

/**
 * "Evènement".
 */
class Event implements EventInterface
{
    /**
     * All Taxes Include : prices include taxes, no additional taxes computed
     */
    const VAT_MODE_ATI = 'ati';

    /**
     * Exclusive of Taxes : prices don't includes taxes, taxes are computed from prices
     */
    const VAT_MODE_ET  = 'et';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var string
     */
    private $timeZone;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var array
     */
    private $locales = [];

    /**
     * @var string
     */
    private $fallback;

    /**
     * @var string
     */
    private $organiserName;

    /**
     * @var Address
     */
    private $paymentAddress;

    /**
     * @var string
     */
    private $organiserEmail;

    /**
     * @var string
     */
    private $bankInfo;

    /**
     * @var string
     */
    private $legalInformation;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var float
     */
    private $vat;

    /**
     * @var string
     */
    private $elementToJoinWithInvoice;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var string
     */
    private $assetPath;

    /**
     * ISO 3166-1 alpha-2 country code
     *
     * @var string
     */
    private $country;

    /**
     * ISO 4217 3-letter currency code
     *
     * @var string
     */
    private $currency;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations  = new ArrayCollection();
        $this->configuration = new Configuration('', '', '');
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getDescription() : '';
    }

    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get locales.
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Set locales.
     *
     * @param array  $locales
     * @param string $fallback
     *
     * @return Event
     */
    public function setLocales(array $locales, $fallback = null)
    {
        $this->locales  = $locales;
        $this->fallback = $fallback ? $fallback : reset($locales);

        return $this;
    }

    /**
     * Get fallback.
     *
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getAvailableLocale($locale)
    {
        if (in_array($locale, $this->getLocales())) {
            return $locale;
        }

        return $this->getFallback();
    }

    /**
     * @return string
     */
    public function getAssetPath()
    {
        return $this->assetPath;
    }

    /**
     * @param string $assetPath
     *
     * @return Event
     */
    public function setAssetPath($assetPath)
    {
        $this->assetPath = $assetPath;

        return $this;
    }

    /**
     * Has locale.
     *
     * @param $locale
     *
     * @return bool
     */
    public function hasLocale($locale)
    {
        return in_array($locale, $this->locales);
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @param string $title
     * @param array  $locales
     * @param string $fallback
     * @param string $mode
     * @param float  $vat
     * @param string $country
     * @param string $currency
     */
    public function update($title, array $locales, $fallback, $mode, $vat, $country, $currency)
    {
        $this->title    = $title;
        $this->locales  = $locales;
        $this->fallback = $fallback;
        $this->mode     = $mode;
        $this->vat      = $vat;
        $this->country  = $country;
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getOrganiserName()
    {
        return $this->organiserName;
    }

    /**
     * @return Address
     */
    public function getPaymentAddress()
    {
        return $this->paymentAddress;
    }

    /**
     * @return string
     */
    public function getOrganiserEmail()
    {
        return $this->organiserEmail;
    }

    /**
     * @return string
     */
    public function getBankInfo()
    {
        return $this->bankInfo;
    }

    /**
     * @return string
     */
    public function getLegalInformation()
    {
        return $this->legalInformation;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @return string
     */
    public function getElementToJoinWithInvoice()
    {
        return $this->elementToJoinWithInvoice;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }
}
