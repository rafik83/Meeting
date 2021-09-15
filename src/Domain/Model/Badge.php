<?php

namespace Proximum\Vimeet\Domain\Model;

class Badge
{
    public const FOOTER_SHOW_TYPE = 'type';
    public const FOOTER_SHOW_CATEGORY = 'category';
    public const FOOTER_SHOW_NONE = 'none';

    public const FOOTER_SHOW_POSSIBILITIES = [
        self::FOOTER_SHOW_NONE => self::FOOTER_SHOW_NONE,
        self::FOOTER_SHOW_TYPE => self::FOOTER_SHOW_TYPE,
        self::FOOTER_SHOW_CATEGORY => self::FOOTER_SHOW_CATEGORY,
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
    private $showCountry;

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

    /** @var bool */
    private $isMirrored;

    /** @var string|null */
    private $leftImage;

    /** @var string|null */
    private $rightImage;

    /** @var bool */
    private $isRightImageFullHeight;

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
        bool $showCountry = false,
        array $conditionedByStates = [],
        bool $isMirrored = false,
        ?string $downLeftImage = null,
        ?string $rightImage = null,
        bool $isRightImageFullHeight = false
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
        $this->showCountry = $showCountry;
        $this->isMirrored = $isMirrored;
        $this->leftImage = $downLeftImage;
        $this->rightImage = $rightImage;
        $this->isRightImageFullHeight = $isRightImageFullHeight;
    }

    public static function createDefault(Event $event, Type $type): self
    {
        return new self($event, $type);
    }

    public function update(
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
        bool $showCountry = false,
        array $conditionedByStates = [],
        bool $isMirrored = false,
        ?string $leftImage = null,
        ?string $rightImage = null,
        bool $isRightImageFullHeight = false
    ): void {
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
        $this->showCountry = $showCountry;
        $this->isMirrored = $isMirrored;
        $this->leftImage = $leftImage;
        $this->rightImage = $rightImage;
        $this->isRightImageFullHeight = $isRightImageFullHeight;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function isShowHeader(): bool
    {
        return $this->showHeader;
    }

    public function isShowCountry(): bool
    {
        return $this->showCountry;
    }

    public function getShowFooterTypeOrCategory(): string
    {
        return $this->showFooterTypeOrCategory;
    }

    public function isShowFooterTypeOrCategory()
    {
        return self::FOOTER_SHOW_NONE !== $this->showFooterTypeOrCategory;
    }

    public function isShowFooterType()
    {
        return self::FOOTER_SHOW_TYPE === $this->showFooterTypeOrCategory;
    }

    public function getFooterTextColor(): string
    {
        return $this->footerTextColor;
    }

    public function getFooterColor(): string
    {
        return $this->footerColor;
    }

    public function isShowPosition(): bool
    {
        return $this->showPosition;
    }

    public function isShowFirstName(): bool
    {
        return $this->showFirstName;
    }

    public function isShowLastName(): bool
    {
        return $this->showLastName;
    }

    public function isShowSheetTitle(): bool
    {
        return $this->showSheetTitle;
    }

    public function isShowQRCode(): bool
    {
        return $this->showQRCode;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function isConditioned(): bool
    {
        return $this->conditioned;
    }

    public function isConditionedByPackage(): bool
    {
        return $this->conditionedByPackage;
    }

    public function getConditionedByStates(): array
    {
        return $this->conditionedByStates;
    }

    public function isMirrored(): bool
    {
        return $this->isMirrored;
    }

    public function getLeftImage(): ?string
    {
        return $this->leftImage;
    }

    public function getRightImage(): ?string
    {
        return $this->rightImage;
    }

    public function isRightImageFullHeight(): bool
    {
        return $this->isRightImageFullHeight;
    }
}
