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
use Proximum\Vimeet\Domain\Model\Event\Day;

/**
 * "Evènement".
 */
class Event implements EventInterface, TraceableInterface
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
     * @var string
     */
    private $logoExtension;

    /**
     * @var string|null
     */
    private $emailTeam;

    /**
     * @var ArrayCollection
     */
    private $days;

    /**
     * @param string      $title
     * @param string      $fallback
     * @param array       $locales
     * @param string      $mode
     * @param float       $vat
     * @param string      $country
     * @param string      $currency
     * @param string      $timeZone
     * @param string      $domain
     * @param string      $organiserName
     * @param string|null $emailTeam
     */
    public function __construct(
        $title,
        $fallback,
        array $locales,
        $mode,
        $vat,
        $country,
        $currency,
        $timeZone,
        $domain,
        $organiserName,
        $emailTeam
    ) {
        $this->translations   = new ArrayCollection();
        $this->configuration  = new Configuration('', '', '');
        $this->paymentAddress = new Address('', '', '', '');
        $this->days           = new ArrayCollection();
        $this->title          = $title;
        $this->fallback       = $fallback;
        $this->locales        = $locales;
        $this->mode           = $mode;
        $this->vat            = $vat;
        $this->country        = $country;
        $this->currency       = $currency;
        $this->timeZone       = $timeZone;
        $this->domain         = $domain;
        $this->organiserName  = $organiserName;
        $this->emailTeam      = $emailTeam;
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
     * @param string $locale
     *
     * @return string
     */
    public function getBankInfo($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getBankInfo() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getBillingAddress($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getBillingAddress() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getPaymentCondition($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getPaymentCondition() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getPaymentFooter($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getPaymentFooter() : '';
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
     * @param string $logoExtension
     */
    public function setLogo($logo, $logoExtension)
    {
        $this->logo          = $logo;
        $this->logoExtension = $logoExtension;
    }

    /**
     * @return string|null
     */
    public function getEmailTeam()
    {
        return $this->emailTeam;
    }

    /**
     * @param string      $title
     * @param array       $locales
     * @param string      $fallback
     * @param string      $mode
     * @param float       $vat
     * @param string      $country
     * @param string      $currency
     * @param string      $timeZone
     * @param string      $domain
     * @param string      $organiserName
     * @param string|null $emailTeam
     */
    public function update(
        $title,
        array $locales,
        $fallback,
        $mode,
        $vat,
        $country,
        $currency,
        $timeZone,
        $domain,
        $organiserName,
        $emailTeam
    ) {
        $this->title         = $title;
        $this->locales       = $locales;
        $this->fallback      = $fallback;
        $this->mode          = $mode;
        $this->vat           = $vat;
        $this->country       = $country;
        $this->currency      = $currency;
        $this->timeZone      = $timeZone;
        $this->domain        = $domain;
        $this->organiserName = $organiserName;
        $this->emailTeam     = $emailTeam;
    }

    /**
     * @param string $organiserName
     * @param string $organiserEmail
     */
    public function updateOrganiserInfo($organiserName, $organiserEmail)
    {
        $this->organiserName  = $organiserName;
        $this->organiserEmail = $organiserEmail;
    }

    /**
     * @return string
     */
    public function getOrganiserName()
    {
        return $this->organiserName;
    }

    /**
     * @param string $organiserName
     *
     * @return Event
     */
    public function setOrganiserName($organiserName)
    {
        $this->organiserName = $organiserName;

        return $this;
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
     * @param string $organiserEmail
     *
     * @return $this
     */
    public function setOrganiserEmail($organiserEmail)
    {
        $this->organiserEmail = $organiserEmail;

        return $this;
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

    /**
     * @return bool
     */
    public function isSvgLogo()
    {
        return $this->logoExtension === 'svg';
    }

    /**
     * @return Event\Day[]
     */
    public function getDays()
    {
        $days = $this->days->toArray();

        usort($days, function (Day $day1, Day $day2) {
            return $day1->getDay() > $day2->getDay();
        });

        return $days;
    }

    /**
     * @return string
     */
    public function getTraceableName()
    {
        return 'event';
    }
}
