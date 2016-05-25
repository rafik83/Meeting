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
use Doctrine\Common\Collections\Criteria;

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
     * Get ordered packages
     *
     * @return Product[]
     */
    public function getPackages()
    {
        return $this
            ->packages
            ->matching(Criteria::create()->orderBy(['rank' => Criteria::ASC]))
            ->map(function (PurchasingFunnelPackage $package) { return $package->getPackage(); })
            ->toArray();
    }

    /**
     * Get ordered options
     *
     * @return Product[]
     */
    public function getOptions()
    {
        return $this
            ->options
            ->matching(Criteria::create()->orderBy(['rank' => Criteria::ASC]))
            ->map(function (PurchasingFunnelOption $option) { return $option->getOption(); })
            ->toArray();
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
     * @param Product $package
     *
     * @return bool
     */
    public function hasPackage(Product $package)
    {
        return $this->packages->exists(function (PurchasingFunnelPackage $pfp) use ($package) {
            return $pfp->getPackage() === $package;
        });
    }

    /**
     * @param array $packages
     *
     * @return PurchasingFunnel
     */
    public function choosePackages(array $packages)
    {
        // Remove delete packages and update rank
        foreach ($this->packages as $package) {
            $rank = $package instanceof PurchasingFunnelPackage && array_search($package->getPackage(), $packages);
            if (false === $rank) {
                $this->packages->removeElement($package);
            } else {
                $package->setRank($rank);
            }
        }

        // Add new package
        foreach ($packages as $rank => $package) {
            if (!$this->hasPackage($package)) {
                $this->packages->add(new PurchasingFunnelPackage($this, $package, $rank));
            }
        }

        return $this;
    }

    /**
     * @param Product $option
     *
     * @return bool
     */
    public function hasOption(Product $option)
    {
        return $this->options->exists(function (PurchasingFunnelOption $pfp) use ($option) {
            return $pfp->getOption() === $option;
        });
    }

    /**
     * @param Product[] $options
     *
     * @return PurchasingFunnel
     */
    public function chooseOptions(array $options)
    {
        // Remove delete options and update rank
        foreach ($this->options as $option) {
            $rank = $option instanceof PurchasingFunnelOption && array_search($option->getOption(), $options);
            if (false === $rank) {
                $this->options->removeElement($option);
            } else {
                $option->setRank($rank);
            }
        }

        // Add new option
        foreach ($options as $rank => $option) {
            if (!$this->hasOption($option)) {
                $this->options->add(new PurchasingFunnelOption($this, $option, $rank));
            }
        }

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
}
