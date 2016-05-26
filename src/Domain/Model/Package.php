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
     * @var string
     */
    private $title;

    /**
     * @var ArrayCollection
     */
    private $plans;

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
    private $plansEnabled = true;

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
     * Package constructor.
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
        $this->plans        = new ArrayCollection();
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
     * @return Package
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get ordered plans
     *
     * @return Product[]
     */
    public function getPlans()
    {
        return $this
            ->plans
            ->matching(Criteria::create()->orderBy(['rank' => Criteria::ASC]))
            ->map(function (PackagePlan $plan) { return $plan->getPlan(); })
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
            ->map(function (PackageOption $option) { return $option->getOption(); })
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
     * Get plansEnabled
     *
     * @return boolean
     */
    public function isPlansEnabled()
    {
        return $this->plansEnabled;
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
     * @param string $plansLabel
     * @param string $participantAndPlanningLabel
     * @param string $optionsLabel
     *
     * @return Package
     */
    public function translate($locale, $plansLabel, $participantAndPlanningLabel, $optionsLabel)
    {
        if (!$this->translations->containsKey($locale)) {
            $this->translations->add(new PackageTranslation($this, $locale, $plansLabel, $participantAndPlanningLabel, $optionsLabel));
        } else {
            $this->translations->get($locale)->set($plansLabel, $participantAndPlanningLabel, $optionsLabel);
        }

        return $this;
    }

    /**
     * @param bool $plansEnabled
     * @param bool $participantAndPlanningEnabled
     * @param bool $optionsEnabled
     *
     * @return Package
     */
    public function enable($plansEnabled, $participantAndPlanningEnabled, $optionsEnabled)
    {
        $this->plansEnabled               = $plansEnabled;
        $this->participantAndPlanningEnabled = $participantAndPlanningEnabled;
        $this->optionsEnabled                = $optionsEnabled;

        return $this;
    }

    /**
     * @param Product $plan
     *
     * @return bool
     */
    public function hasPlan(Product $plan)
    {
        return $this->plans->exists(function ($key, PackagePlan $pfp) use ($plan) {
            return $pfp->getPlan() === $plan;
        });
    }

    /**
     * @param array $plans
     *
     * @return Package
     */
    public function choosePlans(array $plans)
    {
        // Remove delete plans and update rank
        foreach ($this->plans as $plan) {
            $rank = $plan instanceof PackagePlan && array_search($plan->getPlan(), $plans);
            if (false === $rank) {
                $this->plans->removeElement($plan);
            } else {
                $plan->setRank($rank);
            }
        }

        // Add new plan
        foreach ($plans as $rank => $plan) {
            if (!$this->hasPlan($plan)) {
                $this->plans->add(new PackagePlan($this, $plan, $rank));
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
        return $this->options->exists(function ($key, PackageOption $pfp) use ($option) {
            return $pfp->getOption() === $option;
        });
    }

    /**
     * @param Product[] $options
     *
     * @return Package
     */
    public function chooseOptions(array $options)
    {
        // Remove delete options and update rank
        foreach ($this->options as $option) {
            $rank = $option instanceof PackageOption && array_search($option->getOption(), $options);
            if (false === $rank) {
                $this->options->removeElement($option);
            } else {
                $option->setRank($rank);
            }
        }

        // Add new option
        foreach ($options as $rank => $option) {
            if (!$this->hasOption($option)) {
                $this->options->add(new PackageOption($this, $option, $rank));
            }
        }

        return $this;
    }

    /**
     * Set planning
     *
     * @param Product $planning
     *
     * @return Package
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
     * @return Package
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
    public function getPlansLabel($locale)
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getPlansLabel()
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
