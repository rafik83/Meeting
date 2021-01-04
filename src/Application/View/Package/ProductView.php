<?php

namespace Proximum\Vimeet\Application\View\Package;

class ProductView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var string
     */
    public $heading;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $addon;

    /**
     * @var string
     */
    public $image;

    /**
     * @var int
     */
    public $availabilityCurrent;

    /**
     * @var int
     */
    public $availabilityMax;

    /**
     * @var bool
     */
    public $isOutOfStock;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $subjectedToValidationHelp;

    /**
     * @var bool
     */
    public $isSubjectedToValidation;

    /**
     * @var int
     */
    public $included;

    /**
     * @var bool
     */
    public $isBuyable;

    /**
     * @var string[]
     */
    public $participantsCompleteNameWithAttributedProduct;

    /**
     * @param int      $id
     * @param string   $title
     * @param float    $unitPrice
     * @param string   $heading
     * @param string   $description
     * @param string   $addon
     * @param string   $image
     * @param int      $availabilityCurrent
     * @param int      $availabilityMax
     * @param bool     $isOutOfStock
     * @param string   $vatMode
     * @param string   $currency
     * @param string   $subjectedToValidationHelp
     * @param bool     $isSubjectedToValidation
     * @param int      $included
     * @param bool     $isBuyable
     * @param string[] $participantsCompleteNameWithAttributedProduct
     */
    public function __construct(
        $id,
        $title,
        $unitPrice,
        $heading,
        $description,
        $addon,
        $image,
        $availabilityCurrent,
        $availabilityMax,
        $isOutOfStock,
        $vatMode,
        $currency,
        $subjectedToValidationHelp,
        $isSubjectedToValidation,
        $included,
        $isBuyable,
        array $participantsCompleteNameWithAttributedProduct = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->unitPrice = $unitPrice;
        $this->heading = $heading;
        $this->description = $description;
        $this->addon = $addon;
        $this->image = $image;
        $this->availabilityCurrent = $availabilityCurrent;
        $this->availabilityMax = $availabilityMax;
        $this->isOutOfStock = $isOutOfStock;
        $this->vatMode = $vatMode;
        $this->currency = $currency;
        $this->subjectedToValidationHelp = $subjectedToValidationHelp;
        $this->isSubjectedToValidation = $isSubjectedToValidation;
        $this->included = $included;
        $this->isBuyable = $isBuyable;
        $this->participantsCompleteNameWithAttributedProduct = $participantsCompleteNameWithAttributedProduct;
    }
}
