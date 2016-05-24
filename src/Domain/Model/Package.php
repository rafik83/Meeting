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
}
