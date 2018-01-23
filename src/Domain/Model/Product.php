<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Product\Feature;
use Proximum\Vimeet\Domain\Model\Product\ProductIncluded;

/**
 * "Produit".
 *
 * A product can be bought buy a sheet.
 * A product can be a plan, a participant, a planning or an option.
 * Each product can be included in a package.
 */
class Product
{
    const TYPE_PLAN        = 'plan';
    const TYPE_OPTION      = 'option';
    const TYPE_PARTICIPANT = 'participant';
    const TYPE_PLANNING    = 'planning';
    /**
     * Ceil in percentage
     */
    const AVAILABILITY_CEIL_WARNING = 30;
    /**
     * Ceil in percentage
     */
    const AVAILABILITY_CEIL_ALERT     = 10;
    const AVAILABILITY_STATUS_DEFAULT = 'default';
    const AVAILABILITY_STATUS_WARNING = 'warning';
    const AVAILABILITY_STATUS_ALERT   = 'alert';

    /**
     * @var int
     */
    private $id;

    /**
     * One of the TYPE_* const
     *
     * @var string
     */
    private $type;

    /**
     * Which event the product is available for.
     *
     * @var Event
     */
    private $event;

    /**
     * Product title and description translations.
     *
     * @var ArrayCollection
     */
    private $translations;

    /**
     * Product internal name.
     *
     * @var string
     */
    private $name;

    /**
     * Product image.
     *
     * @var string
     */
    private $image;

    /**
     * Unit price.
     *
     * @var float;
     */
    private $unitPrice;

    /**
     * Maximum quantity a sheet can bought.
     *
     * @var int
     */
    private $quantityMax;

    /**
     * How many product is currently available.
     *
     * @var int
     */
    private $availabilityCurrent;

    /**
     * How many product was available.
     *
     * @var int
     */
    private $availabilityMax;

    /**
     * Can the sheet update the quantity for this product he bought.
     *
     * @var bool
     */
    private $updatable;

    /**
     * The date the product quantity can be deletable until.
     *
     * @var \DateTimeInterface
     */
    private $deletableUntil;

    /**
     * Date how far the product can be sold
     *
     * @var \DateTimeInterface
     */
    private $buyableUntil;

    /**
     * Product features.
     *
     * @var ArrayCollection
     */
    private $features;

    /**
     * Products freely included in this product
     *
     * @var ArrayCollection
     */
    private $productIncluded;

    /**
     * "Produit soumis à validation"
     *
     * @var boolean
     */
    private $subjectedToValidation = false;

    /**
     * @param Event                   $event
     * @param string                  $type
     * @param string                  $name
     * @param string                  $image
     * @param float                   $unitPrice
     * @param int                     $quantityMax
     * @param int                     $availabilityCurrent
     * @param int                     $availabilityMax
     * @param bool                    $updatable
     * @param null|\DateTimeInterface $deletableUntil
     * @param bool                    $subjectedToValidation
     * @param null|\DateTimeInterface $buyableUntil
     */
    public function __construct(
        Event $event,
        $type,
        $name,
        $image,
        $unitPrice,
        $quantityMax,
        $availabilityCurrent,
        $availabilityMax,
        $updatable,
        \DateTimeInterface $deletableUntil = null,
        $subjectedToValidation = false,
        \DateTimeInterface $buyableUntil = null
    ) {
        $this->translations          = new ArrayCollection();
        $this->features              = new ArrayCollection();
        $this->productIncluded       = new ArrayCollection();
        $this->event                 = $event;
        $this->type                  = $type;
        $this->name                  = $name;
        $this->image                 = $image;
        $this->unitPrice             = $unitPrice;
        $this->quantityMax           = $quantityMax;
        $this->availabilityCurrent   = $availabilityCurrent;
        $this->availabilityMax       = $availabilityMax;
        $this->updatable             = $updatable;
        $this->deletableUntil        = $deletableUntil;
        $this->subjectedToValidation = $subjectedToValidation;
        $this->buyableUntil          = $buyableUntil;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isPlan()
    {
        return $this->type === self::TYPE_PLAN;
    }

    /**
     * @return bool
     */
    public function isOption()
    {
        return $this->type === self::TYPE_OPTION;
    }

    /**
     * @return bool
     */
    public function isParticipant()
    {
        return $this->type === self::TYPE_PARTICIPANT;
    }

    /**
     * @return bool
     */
    public function isPlanning()
    {
        return $this->type === self::TYPE_PLANNING;
    }

    /**
     * @return ProductTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    /**
     * @param ArrayCollection $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return array
     */
    public function getTranslationsData()
    {
        $data = [];

        foreach ($this->translations->toArray() as $locale => $translation) {
            $data[$locale] = $translation->getData();
        }

        return $data;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $heading
     * @param string $description
     * @param string $addon
     * @param string $subjectedToValidationHelp
     *
     * @return Product
     */
    public function translate($locale, $title, $heading, $description, $addon, $subjectedToValidationHelp)
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($title, $heading, $description, $addon, $subjectedToValidationHelp);
        } else {
            $this->translations->set(
                $locale,
                new ProductTranslation(
                    $this,
                    $locale,
                    $title,
                    $heading,
                    $description,
                    $addon,
                    $subjectedToValidationHelp
                )
            );
        }

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getTitle() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getDescription() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getAddon($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getAddon() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getHeading($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getHeading() : '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @return int
     */
    public function getQuantityMax()
    {
        return null === $this->quantityMax ? INF : $this->quantityMax;
    }

    /**
     * @return int|null
     */
    public function getRawQuantityMax()
    {
        return $this->quantityMax;
    }

    /**
     * @return int
     */
    public function getAvailabilityCurrent()
    {
        return $this->availabilityCurrent;
    }

    /**
     * @return int
     */
    public function getAvailabilityMax()
    {
        return $this->availabilityMax;
    }

    /**
     * @return int
     */
    public function getAvailability()
    {
        return $this->getAvailabilityMax() ? $this->getAvailabilityCurrent() : INF;
    }

    /**
     * @return bool
     */
    public function isAvailabilityManaged()
    {
        return $this->getAvailabilityMax() > 0;
    }

    /**
     * @return boolean
     */
    public function isUpdatable()
    {
        return $this->updatable;
    }

    /**
     * @return null|\DateTimeInterface
     */
    public function getDeletableUntil()
    {
        return $this->deletableUntil;
    }

    /**
     * @return null|\DateTimeInterface
     */
    public function getBuyableUntil()
    {
        return $this->buyableUntil;
    }

    /**
     * Get subjectedToValidation
     *
     * @return boolean
     */
    public function isSubjectedToValidation()
    {
        return $this->subjectedToValidation;
    }

    /**
     * Get subjectedToValidationHelp
     *
     * @param string $locale
     *
     * @return string
     */
    public function getSubjectedToValidationHelp($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getSubjectedToValidationHelp() : '';
    }

    /**
     * @return Feature[]
     */
    public function getFeatures()
    {
        return $this->features->toArray();
    }

    /**
     * @param Feature $feature
     *
     * @return Product
     */
    public function addFeature(Feature $feature)
    {
        $this->features->add($feature);

        return $this;
    }

    /**
     * @param Feature $feature
     */
    public function removeFeature(Feature $feature)
    {
        $this->features->removeElement($feature);
    }

    /**
     * @deprecated Use getIncludedProducts instead
     *
     * @return ProductIncluded[]
     */
    public function getProductIncluded()
    {
        return $this->getIncludedProducts();
    }

    /**
     * @param Product $product
     * @param int     $quantity
     *
     * @return Product
     */
    public function includeProduct(Product $product, $quantity)
    {
        $includedProduct = $this->getIncludedProduct($product);

        if ($includedProduct instanceof ProductIncluded) {
            $includedProduct->setQuantity($quantity);
        } else {
            $this->productIncluded->add(new ProductIncluded($this, $product, $quantity));
        }

        return $this;
    }

    /**
     * @param ProductIncluded $product
     *
     * @return Product
     */
    public function removeIncludeProduct(ProductIncluded $product)
    {
        $this->productIncluded->removeElement($product);

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    protected function hasTranslation($locale)
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * @param string $locale
     *
     * @return ProductTranslation
     */
    protected function getTranslation($locale)
    {
        return $this->translations->get($locale);
    }

    /**
     * @return bool
     */
    public function hasIncludedProducts()
    {
        return !$this->productIncluded->isEmpty();
    }

    /**
     * @return ProductIncluded[]
     */
    public function getIncludedProducts()
    {
        return $this->productIncluded->toArray();
    }

    /**
     * Get the number of participant included in this product
     *
     * @return int
     */
    public function getIncludedParticipantQuantity()
    {
        $included = $this->getIncludedParticipantProduct();

        return $included ? $included->getQuantity() : 0;
    }

    /**
     * @return boolean|ProductIncluded
     */
    public function getIncludedParticipantProduct()
    {
        return $this->productIncluded->filter(function (ProductIncluded $productIncluded) {
            return $productIncluded->getIncluded()->isParticipant();
        })->first();
    }

    /**
     * Get the number of planning included in this product
     *
     * @return int
     */
    public function getIncludedPlanningQuantity()
    {
        $included = $this->getIncludedPlanningProduct();

        return $included ? $included->getQuantity() : 0;
    }

    /**
     * @return boolean|ProductIncluded
     */
    public function getIncludedPlanningProduct()
    {
        return $this->productIncluded->filter(function (ProductIncluded $productIncluded) {
            return $productIncluded->getIncluded()->isPlanning();
        })->first();
    }

    /**
     * @return ProductIncluded[]
     */
    public function getIncludedOptionProduct()
    {
        return $this->productIncluded->filter(function (ProductIncluded $productIncluded) {
            return $productIncluded->getIncluded()->isOption();
        })->toArray();
    }

    /**
     * @param int $productId
     *
     * @return null|ProductIncluded
     */
    public function getIncludedOptionById($productId)
    {
        foreach ($this->getIncludedOptionProduct() as $option) {
            if ($option->getIncluded()->getId() === $productId) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getVatMode()
    {
        return $this->getEvent()->getMode();
    }

    /**
     * @return bool
     */
    public function isOutOfStock()
    {
        return $this->getAvailabilityMax() && !$this->getAvailabilityCurrent();
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->event->getCurrency();
    }

    /**
     * @param Event  $event
     * @param string $name
     * @param string $image
     * @param int    $unitPrice
     * @param int    $availabilityCurrent
     * @param int    $availabilityMax
     *
     * @return Product
     */
    public static function createPlan(Event $event, $name, $image, $unitPrice, $availabilityCurrent, $availabilityMax)
    {
        return new self(
            $event,
            Product::TYPE_PLAN,
            $name,
            $image,
            $unitPrice,
            1,
            (int) $availabilityCurrent,
            (int) $availabilityMax,
            false,
            null
        );
    }

    /**
     * @param string      $name
     * @param null|string $image
     * @param int         $availabilityCurrent
     * @param int         $availabilityMax
     * @param float       $unitPrice
     *
     * @return Product
     */
    public function updatePlan($name, $image, $availabilityCurrent, $availabilityMax, $unitPrice)
    {
        $this->name                = $name;
        $this->availabilityCurrent = $availabilityCurrent;
        $this->availabilityMax     = $availabilityMax;
        $this->unitPrice           = $unitPrice;

        if (null !== $image) {
            $this->image = $image;
        }

        return $this;
    }

    /**
     * @param Event  $event
     * @param string $name
     * @param int    $unitPrice
     * @param int    $quantityMax
     *
     * @return Product
     */
    public static function createParticipant(Event $event, $name, $unitPrice, $quantityMax)
    {
        return new self(
            $event,
            Product::TYPE_PARTICIPANT,
            $name,
            null,
            $unitPrice,
            $quantityMax,
            null,
            null,
            true,
            null
        );
    }

    /**
     * @param string $name
     * @param int    $quantityMax
     * @param float  $unitPrice
     *
     * @return Product
     */
    public function updateParticipant($name, $quantityMax, $unitPrice)
    {
        $this->name        = $name;
        $this->quantityMax = $quantityMax;
        $this->unitPrice   = $unitPrice;

        return $this;
    }

    /**
     * @param Event  $event
     * @param string $name
     * @param int    $unitPrice
     * @param int    $quantityMax
     *
     * @return Product
     */
    public static function createPlanning(Event $event, $name, $unitPrice, $quantityMax)
    {
        return new self(
            $event,
            Product::TYPE_PLANNING,
            $name,
            null,
            $unitPrice,
            $quantityMax,
            null,
            null,
            true,
            null
        );
    }

    /**
     * @param string $name
     * @param int    $quantityMax
     * @param float  $unitPrice
     *
     * @return Product
     */
    public function updatePlanning($name, $quantityMax, $unitPrice)
    {
        $this->name        = $name;
        $this->quantityMax = $quantityMax;
        $this->unitPrice   = $unitPrice;

        return $this;
    }

    /**
     * @param Event                   $event
     * @param string                  $name
     * @param string                  $image
     * @param int                     $unitPrice
     * @param int                     $quantityMax
     * @param int                     $availabilityCurrent
     * @param int                     $availabilityMax
     * @param bool                    $updatable
     * @param null|\DateTimeInterface $deletableUntil
     * @param bool                    $subjectedToValidation
     * @param null|\DateTimeInterface $buyableUntil
     *
     * @return Product
     */
    public static function createOption(
        Event $event,
        $name,
        $image,
        $unitPrice,
        $quantityMax,
        $availabilityCurrent,
        $availabilityMax,
        $updatable,
        \DateTimeInterface $deletableUntil = null,
        $subjectedToValidation = false,
        \DateTimeInterface $buyableUntil = null
    ) {
        return new self(
            $event,
            Product::TYPE_OPTION,
            $name,
            $image,
            $unitPrice,
            $quantityMax,
            (int) $availabilityCurrent,
            (int) $availabilityMax,
            $updatable,
            $deletableUntil,
            $subjectedToValidation,
            $buyableUntil
        );
    }

    /**
     * @param string                  $name
     * @param null|string             $image
     * @param int                     $quantityMax
     * @param int                     $availabilityCurrent
     * @param int                     $availabilityMax
     * @param bool                    $updatable
     * @param null|\DateTimeInterface $deletableUntil
     * @param bool                    $subjectedToValidation
     * @param \DateTimeInterface      $buyableUntil
     * @param null|float              $unitPrice
     *
     * @return Product
     */
    public function updateOption(
        $name,
        $image,
        $quantityMax,
        $availabilityCurrent,
        $availabilityMax,
        $updatable,
        $unitPrice,
        \DateTimeInterface $deletableUntil = null,
        $subjectedToValidation = false,
        \DateTimeInterface $buyableUntil = null
    ) {
        $this->name                  = $name;
        $this->quantityMax           = $quantityMax;
        $this->availabilityCurrent   = $availabilityCurrent;
        $this->availabilityMax       = $availabilityMax;
        $this->updatable             = $updatable;
        $this->deletableUntil        = $deletableUntil;
        $this->subjectedToValidation = $subjectedToValidation;
        $this->buyableUntil          = $buyableUntil;
        $this->unitPrice             = $unitPrice;

        if (null !== $image) {
            $this->image = $image;
        }

        return $this;
    }

    /**
     * @return array
     */
    private function getIncludedProductSerializedData()
    {
        $data = [];

        foreach ($this->productIncluded->toArray() as $productIncluded) {
            $data[] = [
                'quantity' => $productIncluded->getQuantity(),
                'included' => $productIncluded->getIncluded()->getData(),
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'id'               => $this->getId(),
            'type'             => $this->getType(),
            'translations'     => $this->getTranslationsData(),
            'productsIncluded' => $this->getIncludedProductSerializedData(),
        ];
    }

    /**
     * @return string
     */
    public function getSerializedData()
    {
        return json_encode($this->getData());
    }

    /**
     * @param Product $product
     *
     * @return ProductIncluded|null
     */
    public function getIncludedProduct(Product $product)
    {
        foreach ($this->getIncludedProducts() as $productIncluded) {
            if ($productIncluded->getIncluded() === $product) {
                return $productIncluded;
            }
        }

        return null;
    }

    /**
     * @param int $key
     *
     * @return Feature
     */
    public function getFeature($key)
    {
        if (!$this->features->containsKey($key)) {
            $this->features->set($key, new Feature($this));
        }

        return $this->features->get($key);
    }

    /**
     * @param \DateTimeInterface $now
     *
     * @return bool
     */
    public function isBuyable(\DateTimeInterface $now)
    {
        return ($this->buyableUntil === null) || ($now < $this->buyableUntil);
    }

    /**
     * @param \DateTimeInterface $now
     *
     * @return bool
     */
    public function isDeletable(\DateTimeInterface $now)
    {
        return ($this->deletableUntil === null) || ($now < $this->deletableUntil);
    }

    /**
     * @param int $bought
     *
     * @return string
     */
    public function getAvailabilityStatus($bought = 0)
    {
        if (!$this->isAvailabilityManaged()) {
            return self::AVAILABILITY_STATUS_DEFAULT;
        }

        $percentageBought = !$this->availabilityMax
            ? 0
            : 100 * ($this->availabilityMax - $bought) / $this->availabilityMax;

        if ($percentageBought < self::AVAILABILITY_CEIL_ALERT) {
            return self::AVAILABILITY_STATUS_ALERT;
        }

        if ($percentageBought < self::AVAILABILITY_CEIL_WARNING) {
            return self::AVAILABILITY_STATUS_WARNING;
        }

        return self::AVAILABILITY_STATUS_DEFAULT;
    }

    /**
     * @param string                  $type
     * @param Event                   $event
     * @param string                  $name
     * @param null|string             $image
     * @param int                     $unitPrice
     * @param int                     $quantityMax
     * @param int                     $availabilityCurrent
     * @param int                     $availabilityMax
     * @param bool                    $updatable
     * @param \DateTimeInterface|null $deletableUntil
     * @param bool                    $subjectedToValidation
     * @param \DateTimeInterface|null $buyableUntil
     *
     * @return Product
     */
    public static function createProductFromType(
        string $type,
        Event $event,
        string $name,
        string $image = null,
        int $unitPrice,
        int $quantityMax = null,
        int $availabilityCurrent = null,
        int $availabilityMax = null,
        bool $updatable,
        \DateTimeInterface $deletableUntil = null,
        bool $subjectedToValidation = false,
        \DateTimeInterface $buyableUntil = null
    ) {
        if ($type === self::TYPE_OPTION) {
            return self::createOption(
                $event,
                $name,
                $image,
                $unitPrice,
                $quantityMax,
                $availabilityCurrent,
                $availabilityMax,
                $updatable,
                $deletableUntil,
                $subjectedToValidation,
                $buyableUntil
            );
        } elseif ($type === self::TYPE_PARTICIPANT) {
            return self::createParticipant($event, $name, $unitPrice, $quantityMax);
        } elseif ($type === self::TYPE_PLAN) {
            return self::createPlan(
                $event,
                $name,
                $image,
                $unitPrice,
                $availabilityCurrent,
                $availabilityMax
            );
        } elseif ($type === self::TYPE_PLANNING) {
            return self::createPlanning($event, $name, $unitPrice, $quantityMax);
        }
    }
}
