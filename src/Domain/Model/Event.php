<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Trace\TraceableName;

/**
 * "Evènement".
 */
class Event implements EventInterface, TraceableInterface, ChatMessageLinkableInterface
{
    /** All Taxes Include : prices include taxes, no additional taxes computed*/
    const VAT_MODE_ATI = 'ati';

    /** Exclusive of Taxes : prices don't includes taxes, taxes are computed from prices*/
    const VAT_MODE_ET = 'et';

    public const DOMAIN_VALIDATION_REGEX = '/^[a-z0-9-.]+$/';

    /** @var int */
    private $id;

    /** @var string */
    private $domain;

    /** @var string */
    private $title;

    /** @var null|string */
    private $logo;

    /** @var null|string */
    private $invoiceLogo;

    /** @var null|string */
    private $invoiceLogoExtension;

    /** @var string */
    private $timeZone;

    /** @var ArrayCollection */
    private $translations;

    /** @var array */
    private $locales = [];

    /** @var string */
    private $fallback;

    /** @var null|string */
    private $organiserName;

    /** @var Address */
    private $paymentAddress;

    /** @var null|string */
    private $organiserEmail;

    /** @var null|string */
    private $legalInformation;

    /** @var string 'ati'|'et' ; See VAT_MODE_ATI and VAT_MODE_ET const */
    private $mode;

    /** @var float */
    private $vat;

    /** @var null|string */
    private $elementToJoinWithInvoice;

    /** @var Configuration */
    private $configuration;

    /** @var string */
    private $assetPath;

    /** @var string ISO 3166-1 alpha-2 country code */
    private $country;

    /** @var string ISO 4217 3-letter currency code */
    private $currency;

    /** @var null|string */
    private $emailTeam;

    /** @var ArrayCollection */
    private $days;

    /** @var null|Prefix */
    private $invoicePrefix;

    /** @var bool */
    private $externalCatalogEnabled = false;

    /** @var bool */
    private $userAgendaVersionsGenerated;

    /** @var bool */
    private $archived;

    /** @var bool */
    private $visible;

    /** @var null|Event */
    private $duplicatedFrom;

    /** @var bool */
    private $welcomeEnabled;

    /** @var bool */
    private $disabledEmailChanging;

    /** @var bool */
    private $disabledPasswordChanging;

    /** @var bool */
    private $googleLoginEnabled;

    /** @var bool */
    private $linkedinLoginEnabled;

    /** @var bool */
    private $accessControlEnabled;

    /** @var bool */
    private $showCheckinStatus;

    /** @var bool */
    private $autoArchiveWebinar;

    public function __construct(
        string $title,
        string $fallback,
        array $locales,
        string $mode,
        float $vat,
        string $country,
        string $currency,
        string $timeZone,
        string $domain,
        ?string $organiserName,
        ?string $emailTeam,
        Prefix $invoicePrefix,
        bool $visible = true,
        ?Event $duplicatedFrom = null,
        bool $welcomeEnabled = true,
        bool $disabledEmailChanging = false,
        bool $disabledPasswordChanging = false,
        bool $googleLoginEnabled = false,
        bool $linkedinLoginEnabled = false,
        bool $accessControlEnabled = false,
        bool $showCheckinStatus = false,
        bool $autoArchiveWebinar = false
    ) {
        $this->translations = new ArrayCollection();
        $this->configuration = new Configuration();
        $this->paymentAddress = new Address('', '', '', '');
        $this->days = new ArrayCollection();
        $this->title = $title;
        $this->fallback = $fallback;
        $this->locales = $locales;
        $this->mode = $mode;
        $this->vat = $vat;
        $this->country = $country;
        $this->currency = $currency;
        $this->timeZone = $timeZone;
        $this->domain = $domain;
        $this->organiserName = $organiserName;
        $this->emailTeam = $emailTeam;
        $this->invoicePrefix = $invoicePrefix;
        $this->assetPath = '';
        $this->visible = $visible;
        $this->userAgendaVersionsGenerated = false;
        $this->archived = false;
        $this->duplicatedFrom = $duplicatedFrom;
        $this->welcomeEnabled = $welcomeEnabled;
        $this->disabledEmailChanging = $disabledEmailChanging;
        $this->disabledPasswordChanging = $disabledPasswordChanging;
        $this->googleLoginEnabled = $googleLoginEnabled;
        $this->linkedinLoginEnabled = $linkedinLoginEnabled;
        $this->accessControlEnabled = $accessControlEnabled;
        $this->showCheckinStatus = $showCheckinStatus;
        $this->autoArchiveWebinar = $autoArchiveWebinar;
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
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
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
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getDescription() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getBankInfo($locale)
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getBankInfo() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getBillingAddress($locale)
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getBillingAddress() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getPaymentCondition($locale)
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getPaymentCondition() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getPaymentFooter($locale)
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getPaymentFooter() : '';
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
     * @deprecated use getLocaleFallback()
     */
    public function getFallback()
    {
        return $this->getLocaleFallback();
    }

    public function getLocaleFallback(): string
    {
        return $this->fallback;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getAvailableLocale($locale): string
    {
        if (\in_array($locale, $this->getLocales(), true)) {
            return $locale;
        }

        return $this->getLocaleFallback();
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
    public function hasLocale($locale): bool
    {
        return \in_array($locale, $this->locales, true);
    }

    /**
     * @return string
     * @deprecated
     *
     */
    public function getLogo()
    {
        return '';
    }

    /**
     * @param string $logo
     * @param string $logoExtension
     *
     * @deprecated
     *
     */
    public function setLogo($logo, $logoExtension)
    {
    }

    /**
     * @return string
     */
    public function getInvoiceLogo()
    {
        return $this->invoiceLogo;
    }

    /**
     * @param string $invoiceLogo
     * @param string $invoiceLogoExtension
     */
    public function setInvoiceLogo($invoiceLogo, $invoiceLogoExtension)
    {
        $this->invoiceLogo = $invoiceLogo;
        $this->invoiceLogoExtension = $invoiceLogoExtension;
    }

    /**
     * @return string
     */
    public function getInvoiceLogoExtension()
    {
        return $this->invoiceLogoExtension;
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
     * @param null|Prefix $invoicePrefix
     * @param bool        $visible
     * @param bool        $welcomeEnabled
     * @param bool        $disabledEmailChanging
     * @param bool        $disabledPasswordChanging
     * @param bool        $googleLoginEnabled
     * @param bool        $linkedinLoginEnabled
     * @param bool        $accessControlEnabled
     * @param bool        $showCheckinStatus
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
        $emailTeam,
        Prefix $invoicePrefix,
        bool $visible,
        bool $welcomeEnabled,
        bool $disabledEmailChanging,
        bool $disabledPasswordChanging,
        bool $googleLoginEnabled = false,
        bool $linkedinLoginEnabled = false,
        bool $accessControlEnabled = false,
        bool $showCheckinStatus = false,
        bool $autoArchiveWebinar = false
    ) {
        $this->title = $title;
        $this->locales = $locales;
        $this->fallback = $fallback;
        $this->mode = $mode;
        $this->vat = $vat;
        $this->country = $country;
        $this->currency = $currency;
        $this->timeZone = $timeZone;
        $this->domain = $domain;
        $this->organiserName = $organiserName;
        $this->emailTeam = $emailTeam;
        $this->invoicePrefix = $invoicePrefix;
        $this->visible = $visible;
        $this->welcomeEnabled = $welcomeEnabled;
        $this->disabledEmailChanging = $disabledEmailChanging;
        $this->disabledPasswordChanging = $disabledPasswordChanging;
        $this->googleLoginEnabled = $googleLoginEnabled;
        $this->linkedinLoginEnabled = $linkedinLoginEnabled;
        $this->accessControlEnabled = $accessControlEnabled;
        $this->showCheckinStatus = $showCheckinStatus;
        $this->autoArchiveWebinar = $autoArchiveWebinar;
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
     * Get VAT mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set Vat mode to VAT_MODE_ET
     */
    public function setVatModeToExclusiveOfTaxes()
    {
        $this->mode = self::VAT_MODE_ET;
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
     * @return Prefix
     */
    public function getInvoicePrefix()
    {
        return $this->invoicePrefix;
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
     * @param string $locale
     *
     * @return bool
     */
    public function hasSvgLogo(string $locale): bool
    {
        return 'svg' === $this->getLocalizedLogoExtension($locale);
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function hasSvgMobileLogo(string $locale): bool
    {
        return 'svg' === $this->getLocalizedMobileLogoExtension($locale);
    }

    /**
     * @param Event\Day[] $days
     */
    public function setDays(array $days)
    {
        foreach ($days as $day) {
            $this->days->add($day);
        }
    }

    public function addDay(Day $day): void
    {
        if (!$this->days->contains($day)) {
            $this->days->add($day);
        }
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
        return TraceableName::EVENT_TRACEABLE_NAME;
    }

    /**
     * @return bool
     */
    public function hasDay()
    {
        return !empty($this->days->toArray());
    }

    /**
     * @return Event\Day
     * @throws DayNotDefinedException
     *
     */
    public function getFirstDay()
    {
        if (!$this->hasDay()) {
            throw new DayNotDefinedException();
        }

        $days = $this->getDays();

        return reset($days);
    }

    /**
     * @return Event\Day
     * @throws DayNotDefinedException
     *
     */
    public function getLastDay()
    {
        if (!$this->hasDay()) {
            throw new DayNotDefinedException();
        }

        $days = $this->getDays();

        return end($days);
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getOpenDate()
    {
        try {
            return $this->getFirstDay()->getStartTime();
        } catch (DayNotDefinedException $exception) {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function isExternalCatalogEnabled(): bool
    {
        return true === $this->externalCatalogEnabled;
    }

    /**
     * @param bool $state
     *
     * @return Event
     */
    public function setExternalCatalog(bool $state): Event
    {
        $this->externalCatalogEnabled = $state;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUserAgendaVersionsGenerated(): bool
    {
        return $this->userAgendaVersionsGenerated;
    }

    /**
     * @param bool $userAgendaVersionsGenerated
     */
    public function setUserAgendaVersionsGenerated(bool $userAgendaVersionsGenerated)
    {
        $this->userAgendaVersionsGenerated = $userAgendaVersionsGenerated;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * Archive the event
     */
    public function archive()
    {
        $this->archived = true;
    }

    /**
     * Un archive the event
     */
    public function unArchive()
    {
        $this->archived = false;
    }

    /**
     * @return null|Event
     */
    public function getDuplicatedFrom(): ?Event
    {
        return $this->duplicatedFrom;
    }

    /**
     * @return bool
     */
    public function isWelcomeEnabled(): bool
    {
        return $this->welcomeEnabled;
    }

    public function isDisabledEmailChanging(): bool
    {
        return $this->disabledEmailChanging;
    }

    public function isDisabledPasswordChanging(): bool
    {
        return $this->disabledPasswordChanging;
    }

    public function isGoogleLoginEnabled(): bool
    {
        return $this->googleLoginEnabled;
    }

    public function isLinkedinLoginEnabled(): bool
    {
        return $this->linkedinLoginEnabled;
    }

    public function hasOAuth2LoginEnabled(): bool
    {
        return $this->isGoogleLoginEnabled() || $this->isLinkedinLoginEnabled();
    }

    public function isAccessControlEnabled(): bool
    {
        return $this->accessControlEnabled;
    }

    public function showCheckinStatus(): bool
    {
        return $this->showCheckinStatus;
    }

    public function accessControlEnabledAndShowCheckinStatus(): bool
    {
        return $this->showCheckinStatus() && $this->isAccessControlEnabled();
    }

    /**
     * @param \DateTimeInterface $datetime
     *
     * @return bool
     * @throws DayNotDefinedException
     *
     */
    public function isFinished(\DateTimeInterface $datetime): bool
    {
        return $this->getLastDay()->getEndTime() < $datetime;
    }

    public function getLocalizedLogo(string $locale): ?string
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getLogo()
            : null;
    }

    public function getLocalizedMobileLogo(string $locale): ?string
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getMobileLogo()
            : null;
    }

    public function getLocalizedLogoExtension(string $locale): ?string
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getLogoExtension()
            : null;
    }

    public function getLocalizedMobileLogoExtension(string $locale): ?string
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getMobileLogoExtension()
            : null;
    }

    public function getLocalizedNotificationImage(string $locale): ?string
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getNotificationImage()
            : null;
    }

    public function getLocalizedNotificationImageExtension(string $locale): ?string
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getNotificationImageExtension()
            : null;
    }

    public function updateLocalizedLogos(
        string $locale,
        ?string $logo = null,
        ?string $logoExtension = null,
        ?string $mobileLogo = null,
        ?string $mobileLogoExtension = null,
        ?string $notificationImage = null,
        ?string $notificationImageExtension = null
    ): void {
        if ($this->translations->containsKey($locale)) {
            $this->translations
                ->get($locale)
                ->updateLogoAndMobileLogo(
                    $logo,
                    $logoExtension,
                    $mobileLogo,
                    $mobileLogoExtension,
                    $notificationImage,
                    $notificationImageExtension
                );

            return;
        }

        $eventTranslation = new EventTranslation($this, $locale, '');
        $eventTranslation->updateLogoAndMobileLogo(
            $logo,
            $logoExtension,
            $mobileLogo,
            $mobileLogoExtension,
            $notificationImage,
            $notificationImageExtension
        );
        $this->translations->set($locale, $eventTranslation);
    }

    public function getAutoArchiveWebinar(): bool
    {
        return $this->autoArchiveWebinar;
    }

    public function getObjectType(): string
    {
        return ChatMessage::TYPE_NETWORKING;
    }

    public function getEvent(): Event
    {
        return $this;
    }
}
