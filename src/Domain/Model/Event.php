<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /** @var ArrayCollection|Collection */
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

    /** @var bool */
    private $apiKey;

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
        bool $autoArchiveWebinar = false,
        string $apiKey = null
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
        $this->apiKey = $apiKey;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(string $locale): ?string
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getDescription() : '';
    }

    public function getBankInfo(string $locale): ?string
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getBankInfo() : '';
    }

    public function getBillingAddress(string $locale): ?string
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getBillingAddress() : '';
    }

    public function getPaymentCondition(string $locale): ?string
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getPaymentCondition() : '';
    }

    public function getPaymentFooter(string $locale): ?string
    {
        $isTranslatable = $this->translations->containsKey($locale);

        return $isTranslatable ? $this->translations->get($locale)->getPaymentFooter() : '';
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function setLocales(array $locales, string $fallback = null): Event
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

    public function getAvailableLocale(string $locale): string
    {
        if (\in_array($locale, $this->getLocales(), true)) {
            return $locale;
        }

        return $this->getLocaleFallback();
    }

    public function getAssetPath(): string
    {
        return $this->assetPath;
    }

    public function setAssetPath(string $assetPath): Event
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

    public function getInvoiceLogo(): ?string
    {
        return $this->invoiceLogo;
    }

    public function setInvoiceLogo(?string $invoiceLogo, ?string $invoiceLogoExtension)
    {
        $this->invoiceLogo = $invoiceLogo;
        $this->invoiceLogoExtension = $invoiceLogoExtension;
    }

    public function getInvoiceLogoExtension(): ?string
    {
        return $this->invoiceLogoExtension;
    }

    public function getEmailTeam(): ?string
    {
        return $this->emailTeam;
    }

    public function update(
        string $title,
        array $locales,
        string $fallback,
        string $mode,
        float $vat,
        string $country,
        string $currency,
        string $timeZone,
        string $domain,
        ?string $organiserName,
        ?string $emailTeam,
        Prefix $invoicePrefix,
        bool $visible,
        bool $welcomeEnabled,
        bool $disabledEmailChanging,
        bool $disabledPasswordChanging,
        bool $googleLoginEnabled = false,
        bool $linkedinLoginEnabled = false,
        bool $accessControlEnabled = false,
        bool $showCheckinStatus = false,
        bool $autoArchiveWebinar = false,
        ?string $apiKey = null
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
        $this->apiKey = $apiKey;
    }

    public function updateOrganiserInfo(?string $organiserName, ?string $organiserEmail)
    {
        $this->organiserName  = $organiserName;
        $this->organiserEmail = $organiserEmail;
    }

    public function getOrganiserName(): ?string
    {
        return $this->organiserName;
    }

    public function setOrganiserName(?string $organiserName): self
    {
        $this->organiserName = $organiserName;

        return $this;
    }

    public function getPaymentAddress(): Address
    {
        return $this->paymentAddress;
    }

    public function getOrganiserEmail(): ?string
    {
        return $this->organiserEmail;
    }

    public function setOrganiserEmail(?string $organiserEmail): self
    {
        $this->organiserEmail = $organiserEmail;

        return $this;
    }

    public function getLegalInformation(): ?string
    {
        return $this->legalInformation;
    }

    /**
     * Get VAT mode
     */
    public function getMode(): string
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

    public function getVat(): float
    {
        return $this->vat;
    }

    public function getElementToJoinWithInvoice(): string
    {
        return $this->elementToJoinWithInvoice;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getInvoicePrefix(): Prefix
    {
        return $this->invoicePrefix;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    public function hasSvgLogo(string $locale): bool
    {
        return 'svg' === $this->getLocalizedLogoExtension($locale);
    }

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
    public function getDays(): array
    {
        $days = $this->days->toArray();

        usort($days, function (Day $day1, Day $day2) {
            return $day1->getDay() > $day2->getDay();
        });

        return $days;
    }

    public function getTraceableName(): string
    {
        return TraceableName::EVENT_TRACEABLE_NAME;
    }

    public function hasDay(): bool
    {
        return !empty($this->days->toArray());
    }

    /**
     * @throws DayNotDefinedException
     */
    public function getFirstDay(): Event\Day
    {
        if (!$this->hasDay()) {
            throw new DayNotDefinedException();
        }

        $days = $this->getDays();

        return reset($days);
    }

    /**
     * @throws DayNotDefinedException
     */
    public function getLastDay(): Event\Day
    {
        if (!$this->hasDay()) {
            throw new DayNotDefinedException();
        }

        $days = $this->getDays();

        return end($days);
    }

    public function getOpenDate(): ?\DateTimeInterface
    {
        try {
            return $this->getFirstDay()->getStartTime();
        } catch (DayNotDefinedException $exception) {
            return null;
        }
    }

    public function isExternalCatalogEnabled(): bool
    {
        return true === $this->externalCatalogEnabled;
    }

    public function setExternalCatalog(bool $state): Event
    {
        $this->externalCatalogEnabled = $state;

        return $this;
    }

    public function isUserAgendaVersionsGenerated(): bool
    {
        return $this->userAgendaVersionsGenerated;
    }

    public function setUserAgendaVersionsGenerated(bool $userAgendaVersionsGenerated)
    {
        $this->userAgendaVersionsGenerated = $userAgendaVersionsGenerated;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

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

    public function getDuplicatedFrom(): ?Event
    {
        return $this->duplicatedFrom;
    }

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
     * @throws DayNotDefinedException
     */
    public function isFinished(\DateTimeInterface $dateTime): bool
    {
        return $this->getLastDay()->getEndTime() < $dateTime;
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

    public function getApiKey(): ?string
    {
        return $this->apiKey;
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
