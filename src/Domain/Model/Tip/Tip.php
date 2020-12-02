<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Tip;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Tip
{
    /**
     * Translations keys for visible pages @see AdminBundle/Resources/translations/messages.fr.yml
     */
    const TRANS_VISIBLE_CATALOG = 'admin.tip.column.visible.catalog';
    const TRANS_VISIBLE_MEETING_MANAGEMENT = 'admin.tip.column.visible.meeting_management';
    const TRANS_VISIBLE_PRINT_PLANNING = 'admin.tip.column.visible.print_planning';
    const TRANS_VISIBLE_SHEET = 'admin.tip.column.visible.onSheet';
    const TRANS_VISIBLE_AGENDA = 'admin.tip.column.visible.onAgenda';
    const TRANS_VISIBLE_PACKAGE = 'admin.tip.column.visible.onPackage';
    const TRANS_VISIBLE_CONTACTS = 'admin.tip.column.visible.onContacts';
    const TRANS_VISIBLE_PROGRAM = 'admin.tip.column.visible.onProgram';
    const TRANS_VISIBLE_CONFIRMATION_PHONE = 'admin.tip.column.visible.onConfirmationPhone';
    const TRANS_VISIBLE_NETWORKING = 'admin.tip.column.visible.onNetworking';

    const DISPLAY_DEFAULT = 'default';
    const DISPLAY_FIRST_TIME_OPENED = 'first_time_opened';
    const DISPLAY_ALWAYS_OPENED = 'always_opened';
    const DISPLAY_CHOICES = [self::DISPLAY_DEFAULT, self::DISPLAY_FIRST_TIME_OPENED, self::DISPLAY_ALWAYS_OPENED];

    const CONDITION_ON_ORDERS_WITHOUT = 'without';
    const CONDITION_ON_ORDERS_TOTAL_EQUAL_ZERO = 'total_equal_zero';
    const CONDITION_ON_ORDERS_TOTAL_SUPERIOR_ZERO = 'total_superior_zero';
    const CONDITION_ON_ORDERS_CHOICES = [
        self::CONDITION_ON_ORDERS_WITHOUT,
        self::CONDITION_ON_ORDERS_TOTAL_EQUAL_ZERO,
        self::CONDITION_ON_ORDERS_TOTAL_SUPERIOR_ZERO
    ];

    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var Event|null */
    private $event;

    /** @var ArrayCollection */
    private $translations;

    /** @var ArrayCollection */
    private $types;

    /** @var bool */
    private $onMeetingManagement;

    /** @var bool */
    private $onCatalog;

    /** @var bool */
    private $onPrintPlanning;

    /** @var bool */
    private $onSheet;

    /** @var bool */
    private $onProgram;

    /** @var bool */
    private $onAgenda;

    /** @var bool */
    private $onPackage;

    /** @var bool */
    private $onContacts;

    /** @var bool */
    private $onConfirmationPhone;

    /** @var bool */
    public $onNetworking;

    /** @var string */
    private $display;

    /** @var null|bool */
    private $conditionHasCart;

    /** @var null|bool */
    private $conditionHasRemainingToPay;

    /** @var null|bool */
    private $conditionIsPhoneConfirmed;

    /** @var null|bool */
    private $conditionIsCompleteSheet;

    /** @var null|bool */
    private $conditionHasPendingMeetingProposition;

    /** @var null|array */
    private $conditionOnOrders;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * @param string             $title
     * @param Event|null         $event
     * @param bool               $onMeetingManagement
     * @param bool               $onCatalog
     * @param bool               $onPrintPlanning
     * @param bool               $onSheet
     * @param bool               $onAgenda
     * @param bool               $onPackage
     * @param bool               $onContacts
     * @param bool               $onProgram
     * @param bool               $onConfirmationPhone
     * @param bool               $onNetworking
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        $title,
        ?Event $event,
        $onMeetingManagement,
        $onCatalog,
        $onPrintPlanning,
        $onSheet,
        $onAgenda,
        $onPackage,
        $onContacts,
        $onProgram,
        $onConfirmationPhone,
        $onNetworking,
        \DateTimeInterface $createdAt
    ) {
        $this->title               = $title;
        $this->event               = $event;
        $this->onMeetingManagement = $onMeetingManagement;
        $this->onCatalog           = $onCatalog;
        $this->onPrintPlanning     = $onPrintPlanning;
        $this->onSheet             = $onSheet;
        $this->onAgenda            = $onAgenda;
        $this->onPackage           = $onPackage;
        $this->onContacts          = $onContacts;
        $this->onProgram           = $onProgram;
        $this->onConfirmationPhone = $onConfirmationPhone;
        $this->onNetworking        = $onNetworking;
        $this->translations        = new ArrayCollection();
        $this->types               = new ArrayCollection();
        $this->createdAt           = $createdAt;
        $this->display             = self::DISPLAY_DEFAULT;
    }

    /**
     * Update Tip
     *
     * @param string $title
     * @param bool   $onMeetingManagement
     * @param bool   $onCatalog
     * @param bool   $onPrintPlanning
     * @param bool   $onSheet
     * @param bool   $onAgenda
     * @param bool   $onPackage
     * @param bool   $onContacts
     * @param bool   $onProgram
     * @param bool   $onConfirmationPhone
     * @param bool   $onNetworking
     *
     * @return Tip
     */
    public function update(
        $title,
        $onMeetingManagement,
        $onCatalog,
        $onPrintPlanning,
        $onSheet,
        $onAgenda,
        $onPackage,
        $onContacts,
        $onProgram,
        $onConfirmationPhone,
        $onNetworking
    ) {
        $this->title               = $title;
        $this->onMeetingManagement = $onMeetingManagement;
        $this->onCatalog           = $onCatalog;
        $this->onPrintPlanning     = $onPrintPlanning;
        $this->onSheet             = $onSheet;
        $this->onAgenda            = $onAgenda;
        $this->onPackage           = $onPackage;
        $this->onContacts          = $onContacts;
        $this->onProgram           = $onProgram;
        $this->onConfirmationPhone = $onConfirmationPhone;
        $this->onNetworking        = $onNetworking;

        return $this;
    }

    public function updateConditions(
        string $display,
        ?array $conditionOnOrders,
        ?bool $conditionIsCompleteSheet,
        ?bool $conditionIsPhoneConfirmed,
        ?bool $conditionHasRemainingToPay,
        ?bool $conditionHasPendingMeetingProposition,
        ?bool $conditionHasCart
    ) {
        $this->display = $display;
        $this->conditionOnOrders = $conditionOnOrders;
        $this->conditionIsCompleteSheet = $conditionIsCompleteSheet;
        $this->conditionIsPhoneConfirmed = $conditionIsPhoneConfirmed;
        $this->conditionHasRemainingToPay = $conditionHasRemainingToPay;
        $this->conditionHasPendingMeetingProposition = $conditionHasPendingMeetingProposition;
        $this->conditionHasCart = $conditionHasCart;
    }

    /**
     * @param string             $locale
     * @param string             $title
     * @param string             $content
     * @param \DateTimeInterface $dateTime
     *
     * @return Tip $this
     */
    public function translate($locale, $title, $content, \DateTimeInterface $dateTime): self
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($locale, $title, $content);
        } else {
            $this->setTranslation($locale, $title, $content, $dateTime);
        }

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getTranslationTitle($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getTitle() : null;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getTranslationContent($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getContent() : null;
    }

    /**
     * @param string             $locale
     * @param string             $title
     * @param string             $content
     * @param \DateTimeInterface $dateTime
     */
    public function setTranslation($locale, $title, $content, \DateTimeInterface $dateTime)
    {
        $this->translations->set(
            $locale,
            new TipTranslation(
                $this,
                $dateTime,
                $title,
                $locale,
                $content
            )
        );
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function hasTranslation($locale)
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * @param string $locale
     *
     * @return TipTranslation
     */
    public function getTranslation($locale)
    {
        return $this->translations->get($locale);
    }

    /**
     * @param string $locale
     */
    public function removeTranslation($locale)
    {
        $this->translations->remove($locale);
    }

    /**
     * @param Type $type
     */
    public function setType(Type $type)
    {
        $this->types->set($type->getId(), $type);
    }

    /**
     * @param Type $type
     */
    public function addType(Type $type)
    {
        $this->types->add($type);
    }

    /**
     * @param Type $type
     */
    public function removeType(Type $type)
    {
        $this->types->removeElement($type);
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return TipTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    /**
     * @return null|Event
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types->toArray();
    }

    /**
     * @return bool
     */
    public function isOnMeetingManagement()
    {
        return $this->onMeetingManagement;
    }

    /**
     * @return bool
     */
    public function isOnCatalog()
    {
        return $this->onCatalog;
    }

    /**
     * @return bool
     */
    public function isOnPrintPlanning()
    {
        return $this->onPrintPlanning;
    }

    /**
     * @return bool
     */
    public function isOnSheet()
    {
        return $this->onSheet;
    }

    /**
     * @return bool
     */
    public function isOnProgram()
    {
        return $this->onProgram;
    }

    /**
     * @return bool
     */
    public function isOnAgenda()
    {
        return $this->onAgenda;
    }

    public function isOnPackage(): bool
    {
        return $this->onPackage;
    }

    public function isOnContacts(): bool
    {
        return $this->onContacts;
    }

    /**
     * @return bool
     */
    public function isOnConfirmationPhone()
    {
        return $this->onConfirmationPhone;
    }

    public function isOnNetworking(): bool
    {
        return $this->onNetworking;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return array
     */
    public function getPagesTranslations()
    {
        $pagesTranslations = [];

        if ($this->isOnCatalog()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_CATALOG;
        }

        if ($this->isOnMeetingManagement()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_MEETING_MANAGEMENT;
        }

        if ($this->isOnPrintPlanning()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_PRINT_PLANNING;
        }

        if ($this->isOnSheet()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_SHEET;
        }

        if ($this->isOnAgenda()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_AGENDA;
        }

        if ($this->isOnPackage()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_PACKAGE;
        }

        if ($this->isOnContacts()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_CONTACTS;
        }

        if ($this->isOnProgram()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_PROGRAM;
        }

        if ($this->isOnConfirmationPhone()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_CONFIRMATION_PHONE;
        }

        if ($this->isOnNetworking()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_NETWORKING;
        }

        return $pagesTranslations;
    }

    /**
     * @return bool
     */
    public function hasEvent(): bool
    {
        return null !== $this->event;
    }

    /**
     * @return string
     */
    public function getDisplay(): string
    {
        return $this->display;
    }

    public function isDisplayDefault(): bool
    {
        return self::DISPLAY_DEFAULT === $this->display;
    }

    public function isDisplayAlwaysOpened(): bool
    {
        return self::DISPLAY_ALWAYS_OPENED === $this->display;
    }

    public function isDisplayFirstTimeOpened(): bool
    {
        return self::DISPLAY_FIRST_TIME_OPENED === $this->display;
    }

    public function hasConditionCart(): ?bool
    {
        return $this->conditionHasCart;
    }

    public function hasConditionRemainingToPay(): ?bool
    {
        return $this->conditionHasRemainingToPay;
    }

    public function getConditionOnOrders(): ?array
    {
        return $this->conditionOnOrders;
    }

    public function hasConditionPhoneConfirmed(): ?bool
    {
        return $this->conditionIsPhoneConfirmed;
    }

    public function hasConditionCompleteSheet(): ?bool
    {
        return $this->conditionIsCompleteSheet;
    }

    public function hasConditionPendingMeetingProposition(): ?bool
    {
        return $this->conditionHasPendingMeetingProposition;
    }
}
