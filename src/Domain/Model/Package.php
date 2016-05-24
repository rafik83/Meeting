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
use Proximum\Vimeet\Domain\Model\Package\Feature;
use Proximum\Vimeet\Domain\Model\Package\ProductIncluded;

/**
 * "Formule".
 */
class Package
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $image;

    /**
     * @var float;
     */
    private $unitPrice;

    /**
     * @var int
     */
    private $availabilityCurrent;

    /**
     * @var int
     */
    private $availabilityMax;

    /**
     * @var ArrayCollection
     */
    private $features;

    /**
     * @var int
     */
    private $participantIncluded;

    /**
     * @var ArrayCollection
     */
    private $productIncluded;

    /**
     * @param Event  $event
     * @param string $name
     * @param string $image
     * @param float  $unitPrice
     * @param int    $availabilityCurrent
     * @param int    $availabilityMax
     * @param int    $participantIncluded
     */
    public function __construct(
        Event $event,
        $name,
        $image,
        $unitPrice,
        $availabilityCurrent,
        $availabilityMax,
        $participantIncluded
    ) {
        $this->event               = $event;
        $this->translations        = new ArrayCollection();
        $this->features            = new ArrayCollection();
        $this->productIncluded     = new ArrayCollection();
        $this->name                = $name;
        $this->image               = $image;
        $this->unitPrice           = $unitPrice;
        $this->availabilityCurrent = $availabilityCurrent;
        $this->availabilityMax     = $availabilityMax;
        $this->participantIncluded = $participantIncluded;
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
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $descriptionTitle
     * @param string $descriptionContent
     * @param string $optionalPriceText
     *
     * @return Package
     */
    public function translate($locale, $title, $descriptionTitle, $descriptionContent, $optionalPriceText)
    {
        if ($this->translations->get($locale)) {
            $this->translations->get($locale)->set($title, $descriptionTitle, $descriptionContent, $optionalPriceText);
        } else {
            $this->translations->set($locale, new PackageTranslation($this, $locale, $title, $descriptionTitle, $descriptionContent, $optionalPriceText));
        }

        return $this;
    }

    /**
     * @param string             $locale
     * @param PackageTranslation $translation
     */
    public function setTranslation($locale, PackageTranslation $translation)
    {
        $this->translations->set($locale, $translation);
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
     * @return ArrayCollection
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @param Feature $feature
     *
     * @return Package
     */
    public function addFeature(Feature $feature)
    {
        $this->features->add($feature);

        return $this;
    }

    /**
     * @return int
     */
    public function getParticipantIncluded()
    {
        return $this->participantIncluded;
    }

    /**
     * @return ArrayCollection
     */
    public function getProductIncluded()
    {
        return $this->productIncluded;
    }

    /**
     * @param Product $product
     * @param int     $quantity
     *
     * @return Package
     */
    public function includeProduct(Product $product, $quantity)
    {
        $this->productIncluded->add(new ProductIncluded($this, $product, $quantity));

        return $this;
    }
}
