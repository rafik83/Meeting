<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /**
     * @param int                     $id
     * @param string                  $name
     * @param string                  $type
     * @param float                   $unitPrice
     * @param bool                    $subjectedToValidation
     * @param array                   $includedProducts
     * @param int                     $bought
     * @param bool                    $removable
     * @param string                  $availabilityStatus
     * @param bool                    $availabilityManaged
     * @param bool                    $updatable
     * @param float|null              $quantityMax
     * @param float|null              $availabilityCurrent
     * @param float|null              $availabilityMax
     * @param \DateTimeInterface|null $buyableUntil
     * @param \DateTimeInterface|null $deletableUntil
     */
    public function __construct(
        int $id,
        string $name,
        string $type,
        float $unitPrice,
        bool $subjectedToValidation,
        array $includedProducts,
        int $bought,
        bool $removable,
        string $availabilityStatus,
        bool $availabilityManaged,
        bool $updatable,
        float $quantityMax = null,
        float $availabilityCurrent = null,
        float $availabilityMax = null,
        \DateTimeInterface $buyableUntil = null,
        \DateTimeInterface $deletableUntil = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->unitPrice = $unitPrice;
        $this->subjectedToValidation = $subjectedToValidation;
        $this->includedProducts = $includedProducts;
        $this->bought = $bought;
        $this->removable = $removable;
        $this->availabilityStatus = $availabilityStatus;
        $this->availabilityManaged = $availabilityManaged;
        $this->updatable = $updatable;
        $this->quantityMax = $quantityMax;
        $this->availabilityCurrent = $availabilityCurrent;
        $this->availabilityMax = $availabilityMax;
        $this->buyableUntil = $buyableUntil;
        $this->deletableUntil = $deletableUntil;
    }

    /**
     * @return bool
     */
    public function hasIncludedProducts(): bool
    {
        return !empty($this->includedProducts);
    }
}
