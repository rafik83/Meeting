<?php

namespace Proximum\Vimeet\Application\View\Product;

class ProductView
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $type;

    /** @var float */
    public $unitPrice;

    /** @var bool */
    public $subjectedToValidation;

    /** @var array */
    public $includedProducts;

    /** @var int */
    public $bought;

    /** @var int */
    public $productIncludedBought;

    /** @var string */
    public $availabilityStatus;

    /** @var bool */
    public $availabilityManaged;

    /** @var bool */
    public $updatable;

    /** @var float|null */
    public $quantityMax;

    /** @var float|null */
    public $availabilityCurrent;

    /** @var float|null */
    public $availabilityMax;

    /** @var \DateTimeInterface|null */
    public $buyableUntil;

    /** @var \DateTimeInterface|null */
    public $deletableUntil;

    /** @var bool */
    public $removable;

    /** @var bool */
    public $hasAvailabilityTimeRanges;

    /** @var bool */
    public $isAttributable;

    /** @var bool */
    public $hasHappenings;

    public function __construct(
        int $id,
        string $name,
        string $type,
        float $unitPrice,
        bool $subjectedToValidation,
        array $includedProducts,
        int $bought,
        int $productIncludedBought,
        bool $removable,
        string $availabilityStatus,
        bool $availabilityManaged,
        bool $updatable,
        float $quantityMax = null,
        float $availabilityCurrent = null,
        float $availabilityMax = null,
        \DateTimeInterface $buyableUntil = null,
        \DateTimeInterface $deletableUntil = null,
        bool $hasAvailabilityTimeRanges = false,
        bool $isAttributable = false,
        bool $hasHappenings = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->unitPrice = $unitPrice;
        $this->subjectedToValidation = $subjectedToValidation;
        $this->includedProducts = $includedProducts;
        $this->bought = $bought;
        $this->productIncludedBought = $productIncludedBought;
        $this->removable = $removable;
        $this->availabilityStatus = $availabilityStatus;
        $this->availabilityManaged = $availabilityManaged;
        $this->updatable = $updatable;
        $this->quantityMax = $quantityMax;
        $this->availabilityCurrent = $availabilityCurrent;
        $this->availabilityMax = $availabilityMax;
        $this->buyableUntil = $buyableUntil;
        $this->deletableUntil = $deletableUntil;
        $this->hasAvailabilityTimeRanges = $hasAvailabilityTimeRanges;
        $this->isAttributable = $isAttributable;
        $this->hasHappenings = $hasHappenings;
    }

    /**
     * @return bool
     */
    public function hasIncludedProducts(): bool
    {
        return !empty($this->includedProducts);
    }
}
