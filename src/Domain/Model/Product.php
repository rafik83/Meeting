<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * "Produit".
 */
class Product
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
    private $quantityMin;

    /**
     * @var int
     */
    private $quantityMax;

    /**
     * @var int
     */
    private $availabilityCurrent;

    /**
     * @var int
     */
    private $availabilityMax;

    /**
     * @var bool
     */
    private $updatable;

    /**
     * @var \DateTimeInterface
     */
    private $updatableUntil;

    /**
     * @param Event                   $event
     * @param string                  $name
     * @param string                  $image
     * @param float                   $unitPrice
     * @param int                     $quantityMin
     * @param int                     $quantityMax
     * @param int                     $availabilityCurrent
     * @param int                     $availabilityMax
     * @param bool                    $updatable
     * @param \DateTimeInterface|null $updatableUntil
     */
    public function __construct(
        Event $event,
        $name,
        $image,
        $unitPrice,
        $quantityMin,
        $quantityMax,
        $availabilityCurrent,
        $availabilityMax,
        $updatable,
        $updatableUntil
    ) {
        $this->event               = $event;
        $this->translations        = new ArrayCollection();
        $this->name                = $name;
        $this->image               = $image;
        $this->unitPrice           = $unitPrice;
        $this->quantityMin         = $quantityMin;
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
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->getTranslations()->containsKey($locale) ? $this->getTranslations()->get($locale)->getTitle() : '';
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
    public function getQuantityMin()
    {
        return $this->quantityMin;
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
}
