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
     * The date the product quantity can be updated until.
     *
     * @var \DateTimeInterface
     */
    private $updatableUntil;

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
     * @param \DateTimeInterface|null $updatableUntil
     * @param bool                    $subjectedToValidation
     */
    private function __construct(
        Event $event,
        $type,
        $name,
        $image,
        $unitPrice,
        $quantityMax,
        $availabilityCurrent,
        $availabilityMax,
        $updatable,
        \DateTimeInterface $updatableUntil = null,
        $subjectedToValidation = false
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
        $this->updatableUntil        = $updatableUntil;
        $this->subjectedToValidation = $subjectedToValidation;
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
     * @param string $locale
     * @param string $title
     * @param string $heading
     * @param string $description
     * @param string $addon
     *
     * @return Product
     */
    public function translate($locale, $title, $heading, $description, $addon)
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($title, $heading, $description, $addon);
        } else {
            $this->translations->set($locale, new ProductTranslation($this, $locale, $title, $heading, $description, $addon));
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
     * @return boolean
     */
    public function isUpdatable()
    {
        return $this->updatable;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatableUntil()
    {
        return $this->updatableUntil;
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
     * @return ArrayCollection
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
        $this->productIncluded->add(new ProductIncluded($this, $product, $quantity));

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
            $availabilityCurrent,
            $availabilityMax,
            false,
            null
        );
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
     * @param Event              $event
     * @param string             $name
     * @param string             $image
     * @param int                $unitPrice
     * @param int                $quantityMax
     * @param int                $availabilityCurrent
     * @param int                $availabilityMax
     * @param bool               $updatable
     * @param \DateTimeInterface $updatableUntil
     * @param bool               $subjectedToValidation
     *
     * @return Product
     */
    public static function createOption(Event $event, $name, $image, $unitPrice, $quantityMax, $availabilityCurrent, $availabilityMax, $updatable, \DateTimeInterface $updatableUntil = null, $subjectedToValidation = false)
    {
        return new self(
            $event,
            Product::TYPE_OPTION,
            $name,
            $image,
            $unitPrice,
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            $updatable,
            $updatableUntil,
            $subjectedToValidation
        );
    }
}
