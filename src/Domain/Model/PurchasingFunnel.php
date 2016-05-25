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

class PurchasingFunnel
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
     * @var string
     */
    private $title;

    /**
     * @var ArrayCollection
     */
    private $packages;

    /**
     * @var Product
     */
    private $participant;

    /**
     * @var Product
     */
    private $planning;

    /**
     * @var ArrayCollection
     */
    private $options;

    /**
     * @var
     */
    private $translations;

    /**
     * @var bool
     */
    private $packagesEnabled = true;

    /**
     * @var bool
     */
    private $participantAndPlanningEnabled = true;

    /**
     * @var bool
     */
    private $optionsEnabled = true;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * PurchasingFunnel constructor.
     *
     * @param Event              $event
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, $title, \DateTimeInterface $createdAt)
    {
        $this->event        = $event;
        $this->title        = $title;
        $this->createdAt    = $createdAt;
        $this->packages     = new ArrayCollection();
        $this->options      = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return PurchasingFunnel
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get packages
     *
     * @return Product[]
     */
    public function getPackages()
    {
        return $this->packages->toArray();
    }

    /**
     * Get options
     *
     * @return Product[]
     */
    public function getOptions()
    {
        return $this->options->toArray();
    }

    /**
     * Get participant
     *
     * @return Product
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Get planning
     *
     * @return Product
     */
    public function getPlanning()
    {
        return $this->planning;
    }

    /**
     * Get packagesEnabled
     *
     * @return boolean
     */
    public function isPackagesEnabled()
    {
        return $this->packagesEnabled;
    }

    /**
     * Get participantAndPlanningEnabled
     *
     * @return boolean
     */
    public function isParticipantAndPlanningEnabled()
    {
        return $this->participantAndPlanningEnabled;
    }

    /**
     * Get optionsEnabled
     *
     * @return boolean
     */
    public function isOptionsEnabled()
    {
        return $this->optionsEnabled;
    }

    /**
     * @param string $locale
     * @param string $packagesLabel
     * @param string $participantAndPlanningLabel
     * @param string $optionsLabel
     *
     * @return PurchasingFunnel
     */
    public function translate($locale, $packagesLabel, $participantAndPlanningLabel, $optionsLabel)
    {
        if (!$this->translations->containsKey($locale)) {
            $this->translations->add(new PurchasingFunnelTranslation($this, $locale, $packagesLabel, $participantAndPlanningLabel, $optionsLabel));
        } else {
            $this->translations->get($locale)->set($packagesLabel, $participantAndPlanningLabel, $optionsLabel);
        }

        return $this;
    }

    /**
     * @param bool $packagesEnabled
     * @param bool $participantAndPlanningEnabled
     * @param bool $optionsEnabled
     *
     * @return PurchasingFunnel
     */
    public function enable($packagesEnabled, $participantAndPlanningEnabled, $optionsEnabled)
    {
        $this->packagesEnabled               = $packagesEnabled;
        $this->participantAndPlanningEnabled = $participantAndPlanningEnabled;
        $this->optionsEnabled                = $optionsEnabled;

        return $this;
    }

    /**
     * @param array $packages
     *
     * @return PurchasingFunnel
     */
    public function choosePackages(array $packages)
    {
        self::setCollectionItems($this->packages, $packages);

        return $this;
    }

    /**
     * @param Product[] $options
     *
     * @return PurchasingFunnel
     */
    public function chooseOptions(array $options)
    {
        self::setCollectionItems($this->options, $options);

        return $this;
    }

    /**
     * Set planning
     *
     * @param Product $planning
     *
     * @return PurchasingFunnel
     */
    public function choosePlanning($planning)
    {
        $this->planning = $planning;

        return $this;
    }

    /**
     * Set participant
     *
     * @param Product $participant
     *
     * @return PurchasingFunnel
     */
    public function chooseParticipant($participant)
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getPackagesLabel($locale)
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getPackagesLabel()
            : null;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getParticipantAndPlanningLabel($locale)
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getParticipantAndPlanningLabel()
            : null;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getOptionsLabel($locale)
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getOptionsLabel()
            : null;
    }

    /**
     * @param ArrayCollection $collection
     * @param array           $items
     */
    private static function setCollectionItems(ArrayCollection $collection, array $items)
    {
        // Remove deleted items
        foreach ($collection as $item) {
            if (!in_array($item, $items)) {
                $collection->removeElement($item);
            }
        }

        // Add new items
        foreach ($items as $item) {
            if (!$collection->contains($item)) {
                $collection->add($item);
            }
        }
    }
}
