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
    private $planRanks;

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
    private $groups;

    /**
     * @var ArrayCollection
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
     * @var Type[]
     */
    private $types;

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
        $this->planRanks    = new ArrayCollection();
        $this->groups       = new ArrayCollection();
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
     * Get createdAt
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
            ->planRanks
            ->matching(Criteria::create()->orderBy(['rank' => Criteria::ASC]))
            ->map(function (PackagePlanRank $plan) { return $plan->getPlan(); })
            ->toArray();
    }

    /**
     * Get ordered groups
     *
     * @return PackageGroup[]
     */
    public function getGroups()
    {
        return $this
            ->groups
            ->matching(Criteria::create()->orderBy(['rank' => Criteria::ASC]))
            ->toArray();
    }

    /**
     * Get options
     *
     * @return Product[]
     */
    public function getOptions()
    {
        return array_reduce($this->groups->toArray(), function ($carry, $item) {
            if ($item instanceof PackageGroup) {
                $carry = array_merge($carry, $item->getOptions());
            }

            return $carry;
        }, []);
    }

    /**
     * Get options not out of stock
     *
     * @return Product[]
     */
    public function getAvailablesOptions()
    {
        return array_filter($this->getOptions(), function (Product $product) {
            return !$product->isOutOfStock();
        });
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
     * Return true if the package has at least a step activated
     * @return bool
     */
    public function isPassable()
    {
        return $this->isPlansEnabled() || $this->isParticipantAndPlanningEnabled() || $this->isOptionsEnabled();
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
        $this->plansEnabled                  = $plansEnabled;
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
        return $this->planRanks->exists(function ($key, PackagePlanRank $pfp) use ($plan) {
            return $pfp->getPlan() === $plan;
        });
    }

    /**
     * @param array $plans
     *
     * @return Package
     */
    public function setPlans(array $plans)
    {
        // Remove delete plans
        foreach ($this->planRanks as $planRank) {

            if (!in_array($planRank->getPlan(), $plans)) {
                $this->planRanks->removeElement($planRank);
            } else {
                $planRank->setRank(array_search($planRank->getPlan(), $plans));
            }
        }

        // Add new plan
        foreach ($plans as $rank => $plan) {
            if (!$this->hasPlan($plan)) {
                $this->planRanks->add(new PackagePlanRank($this, $plan, $rank));
            }
        }

        return $this;
    }

    /**
     * @param int $rank
     *
     * @return PackageGroup
     */
    public function group($rank)
    {
        if (!$this->groups->containsKey($rank)) {
            $this->groups->set($rank, new PackageGroup($this, $rank));
        }

        return $this->groups->get($rank);
    }

    /**
     * Set planning
     *
     * @param Product $planning
     *
     * @return Package
     */
    public function setPlanning($planning)
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
    public function setParticipant($participant)
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

    /**
     * @param array $groupOptions
     * @param array $groupLabels
     *
     * @return Package
     */
    public function setGroups(array $groupOptions, array $groupLabels)
    {
        $this->setGroupsLabels($groupLabels);
        $this->setGroupsOptions($groupOptions);

        foreach ($this->getGroups() as $rank => $group) {
            if (!isset($groupLabels[$rank]) && !isset($groupOptions[$rank])) {
                $this->groups->remove($rank);
            }
        }

        return $this;
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param array $groupLabels
     *
     * @return Package
     */
    protected function setGroupsLabels(array $groupLabels)
    {
        foreach ($groupLabels as $rank => $labels) {
            if (!$this->groups->containsKey($rank)) {
                $this->groups->set($rank, new PackageGroup($this, $rank));
            }

            foreach ($labels as $locale => $label) {
                $this->groups->get($rank)->setRank($rank)->translate($locale, $label);
            }
        }

        return $this;
    }

    /**
     * @param array $groupOptions
     *
     * @return Package
     */
    protected function setGroupsOptions(array $groupOptions)
    {
        foreach ($groupOptions as $rank => $options) {
            if (!$this->groups->containsKey($rank)) {
                $this->groups->set($rank, new PackageGroup($this, $rank));
            }

            $this->groups->get($rank)->setOptions(is_array($options) ? $options : []);
        }

        return $this;
    }
}
