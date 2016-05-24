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
 * A product can be a participant, a planning or an option.
 * Each product can be included in a package.
 */
class Product
{
    const TYPE_PACKAGE     = 'package';
    const TYPE_OPTION      = 'option';
    const TYPE_PARTICIPANT = 'participant';
    const TYPE_PLANNING    = 'planning';

    /**
     * @var int
     */
    private $id;

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
        \DateTimeInterface $updatableUntil = null
    ) {
        $this->translations        = new ArrayCollection();
        $this->event               = $event;
        $this->type                = $type;
        $this->name                = $name;
        $this->image               = $image;
        $this->unitPrice           = $unitPrice;
        $this->quantityMax         = $quantityMax;
        $this->availabilityCurrent = $availabilityCurrent;
        $this->availabilityMax     = $availabilityMax;
        $this->updatable           = $updatable;
        $this->updatableUntil      = $updatableUntil;
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
     * @return ProductTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $descriptionTitle
     * @param string $descriptionContent
     * @param string $optionalPriceText
     *
     * @return Product
     */
    public function translate($locale, $title, $descriptionTitle, $descriptionContent, $optionalPriceText)
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($title, $descriptionTitle, $descriptionContent, $optionalPriceText);
        } else {
            $this->translations->set($locale, new ProductTranslation($this, $locale, $title, $descriptionTitle, $descriptionContent, $optionalPriceText));
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
     * @return ProductIncluded[]
     */
    public function getProductIncluded()
    {
        return $this->productIncluded->toArray();
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
}
