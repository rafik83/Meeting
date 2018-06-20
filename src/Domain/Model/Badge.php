<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class Badge
{
    public const FOOTER_SHOW_TYPE = 'type';
    public const FOOTER_SHOW_CATEGORY = 'category';
    public const FOOTER_SHOW_NONE = 'none';

    public const FOOTER_SHOW_POSSIBILITIES = [
        self::FOOTER_SHOW_NONE,
        self::FOOTER_SHOW_TYPE,
        self::FOOTER_SHOW_CATEGORY,
    ];

    /** @var int|null */
    private $id;

    /** @var Event */
    private $event;

    /** @var Type */
    private $type;

    /** @var string|null */
    private $header;

    /** @var bool */
    private $showHeader;

    /**
     * @var string
     *
     * @see self::FOOTER_SHOW_POSSIBILITIES
     */
    private $showFooterTypeOrCategory;

    /** @var string */
    private $footerTextColor;

    /** @var string */
    private $footerColor;

    /** @var bool */
    private $showPosition;

    /** @var bool */
    private $showFirstName;

    /** @var bool */
    private $showLastName;

    /** @var bool */
    private $showSheetTitle;

    /** @var bool */
    private $showQRCode;

    /** @var bool */
    private $activated;

    /** @var bool */
    private $conditioned;

    /** @var bool */
    private $conditionedByPackage;

    /** @var array */
    private $conditionedByStates;

    public function __construct(
        Event $event,
        Type $type,
        ?string $header = null,
        bool $showHeader = true,
        string $showFooterTypeOrCategory = self::FOOTER_SHOW_TYPE,
        string $footerTextColor = '#ffffff',
        string $footerColor = '#000000',
        bool $showPosition = true,
        bool $showFirstName = true,
        bool $showLastName = true,
        bool $showSheetTitle = true,
        bool $showQRCode = true,
        bool $activated = true,
        bool $conditioned = false,
        bool $conditionedByPackage = false,
        array $conditionedByStates = []
    ) {
        $this->event = $event;
        $this->type = $type;
        $this->header = $header;
        $this->showHeader = $showHeader;
        $this->showFooterTypeOrCategory = $showFooterTypeOrCategory;
        $this->footerTextColor = $footerTextColor;
        $this->footerColor = $footerColor;
        $this->showPosition = $showPosition;
        $this->showFirstName = $showFirstName;
        $this->showLastName = $showLastName;
        $this->showSheetTitle = $showSheetTitle;
        $this->showQRCode = $showQRCode;
        $this->activated = $activated;
        $this->conditioned = $conditioned;
        $this->conditionedByPackage = $conditionedByPackage;
        $this->conditionedByStates = $conditionedByStates;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getHeader(): ?string
    {
        return $this->header;
    }

    /**
     * @return bool
     */
    public function isShowHeader(): bool
    {
        return $this->showHeader;
    }

    /**
     * @return string
     */
    public function getShowFooterTypeOrCategory(): string
    {
        return $this->showFooterTypeOrCategory;
    }

    /**
     * @return string
     */
    public function getFooterTextColor(): string
    {
        return $this->footerTextColor;
    }

    /**
     * @return string
     */
    public function getFooterColor(): string
    {
        return $this->footerColor;
    }

    /**
     * @return bool
     */
    public function isShowPosition(): bool
    {
        return $this->showPosition;
    }

    /**
     * @return bool
     */
    public function isShowFirstName(): bool
    {
        return $this->showFirstName;
    }

    /**
     * @return bool
     */
    public function isShowLastName(): bool
    {
        return $this->showLastName;
    }

    /**
     * @return bool
     */
    public function isShowSheetTitle(): bool
    {
        return $this->showSheetTitle;
    }

    /**
     * @return bool
     */
    public function isShowQRCode(): bool
    {
        return $this->showQRCode;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->activated;
    }

    /**
     * @return bool
     */
    public function isConditioned(): bool
    {
        return $this->conditioned;
    }

    /**
     * @return bool
     */
    public function isConditionedByPackage(): bool
    {
        return $this->conditionedByPackage;
    }

    /**
     * @return array
     */
    public function getConditionedByStates(): array
    {
        return $this->conditionedByStates;
    }
}
