<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class Package
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $title;

    /** @var ArrayCollection */
    private $planRanks;

    /** @var ArrayCollection */
    private $participantRanks;

    /** @var null|Product */
    private $planning;

    /** @var bool */
    private $planningSelectable = true;

    /** @var ArrayCollection */
    private $groups;

    /** @var ArrayCollection */
    private $translations;

    /** @var bool */
    private $plansEnabled = true;

    /** @var bool */
    private $participantAndPlanningEnabled = true;

    /**
     * This is used to explicitly set : "a participant = a planning" without buying a planning for each one
     *
     * @var bool
     */
    private $participantWithPlanning = false;

    /** @var bool */
    private $optionsEnabled = true;

    /** @var null|int */
    private $maxParticipant;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var Type[] */
    private $types;

    /**
     * @param Event              $event
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, $title, \DateTimeInterface $createdAt)
    {
        $this->event = $event;
        $this->title = $title;
        $this->createdAt = $createdAt;
        $this->planRanks = new ArrayCollection();
        $this->participantRanks = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
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
     * @return Product[]
     */
    public function getAvailablePlans(): array
    {
        return array_values(
            array_filter(
                $this->getPlans(),
                function (Product $product) {
                    return !$product->isOutOfStock();
                }
            )
        );
    }

    /**
     * Get ordered participant products
     *
     * @return Product[]
     */
    public function getParticipants(): array
    {
        return $this
            ->participantRanks
            ->matching(Criteria::create()->orderBy(['rank' => Criteria::ASC]))
            ->map(
                function (PackageParticipantRank $packageParticipantRank) {
                    return $packageParticipantRank->getProductParticipant();
                }
            )
            ->toArray()
        ;
    }

    /**
     * @return int|null
     */
    public function getMaxParticipant(): ?int
    {
        return $this->maxParticipant;
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
     * @param PackageGroup[] $groups
     *
     * @return Package
     */
    public function setGroupsModel($groups)
    {
        $this->groups->clear();

        foreach ($groups as $key => $group) {
            $this->groups->set($key, $group);
        }

        return $this;
    }

    /**
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
     * @param $id
     *
     * @return null|Product
     */
    public function getOptionById($id)
    {
        foreach ($this->getOptions() as $option) {
            if ($option->getId() === $id) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Get options not out of stock
     *
     * @param \DateTimeInterface $now
     *
     * @return Product[]
     */
    public function getAvailablesOptions(\DateTimeInterface $now)
    {
        return array_filter($this->getOptions(), function (Product $product) use ($now) {
            return !$product->isOutOfStock() && $product->isBuyable($now);
        });
    }

    /**
     * Get participant product
     *
     * @return Product
     *
     * @deprecated use self::getFirstProductParticipant()
     */
    public function getParticipant()
    {
        return $this->getFirstProductParticipant();
    }

    public function getFirstProductParticipant(): Product
    {
        $firstPackageParticipantRank = $this->participantRanks->first();

        if (!$firstPackageParticipantRank instanceof PackageParticipantRank) {
            throw new \DomainException('Package must return at least one participant product');
        }

        return $firstPackageParticipantRank->getProductParticipant();
    }

    /**
     * @return null|Product
     */
    public function getPlanning(): ?Product
    {
        return $this->planning;
    }

    /**
     * @return Product[]
     */
    public function getAttributableOptions(): array
    {
        $attributableOptions = [];

        /** @var PackageGroup $group */
        foreach ($this->groups as $group) {
            foreach ($group->getOptions() as $option) {
                if ($option->isAttributable()) {
                    $attributableOptions[$option->getId()] = $option;
                }
            }
        }

        return $attributableOptions;
    }

    /**
     * @return bool
     */
    public function isPlansEnabled()
    {
        return $this->plansEnabled;
    }

    /**
     * @return bool
     */
    public function isParticipantAndPlanningEnabled()
    {
        return $this->participantAndPlanningEnabled;
    }

    /**
     * @return bool
     */
    public function isOptionsEnabled()
    {
        return $this->optionsEnabled;
    }

    /**
     * Return true if the package has at least a step activated
     *
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
            $this->translations->set($locale, new PackageTranslation($this, $locale, $plansLabel, $participantAndPlanningLabel, $optionsLabel));
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
     * @param int|null $maxParticipant
     *
     * @return Package
     */
    public function setMaxParticipant(?int $maxParticipant): Package
    {
        $this->maxParticipant = $maxParticipant;

        return $this;
    }

    /**
     * @param Product $plan
     *
     * @return bool
     */
    public function hasPlan(Product $plan): bool
    {
        return $this->planRanks->exists(function ($key, PackagePlanRank $pfp) use ($plan) {
            return $pfp->getPlan() === $plan;
        });
    }

    /**
     * @param Product $participant
     *
     * @return bool
     */
    public function hasParticipant(Product $participant): bool
    {
        return $this->participantRanks->exists(
            function ($key, PackageParticipantRank $packageParticipantRank) use ($participant) {
                return $packageParticipantRank->getProductParticipant() === $participant;
            }
        );
    }

    public function hasAtLeastOneProduct(array $productIds): bool
    {
        foreach ($this->getOptions() as $option) {
            if (\in_array($option->getId(), $productIds, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $plans
     *
     * @return Package
     */
    public function setPlans(array $plans): Package
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
     * @param null|Product $planning
     *
     * @return Package
     */
    public function setPlanning(?Product $planning = null)
    {
        $this->planning = $planning;

        return $this;
    }

    /**
     * @param Product[] $participantProducts
     *
     * @return Package
     */
    public function setParticipants(array $participantProducts): Package
    {
        // Remove deleted participant product
        /** @var PackageParticipantRank $participantRank */
        foreach ($this->participantRanks as $participantRank) {
            if (!in_array($participantRank->getProductParticipant(), $participantProducts)) {
                $this->participantRanks->removeElement($participantRank);
            } else {
                $participantRank->setRank(array_search($participantRank->getProductParticipant(), $participantProducts));
            }
        }

        // Add new participant product
        foreach ($participantProducts as $rank => $participantProduct) {
            if (!$this->hasParticipant($participantProduct)) {
                $this->participantRanks->add(new PackageParticipantRank($this, $participantProduct, $rank));
            }
        }

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getPlansLabel($locale)
    {
        return null !== $this->translations && $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getPlansLabel()
            : '';
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getParticipantAndPlanningLabel($locale)
    {
        return null !== $this->translations && $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getParticipantAndPlanningLabel()
            : '';
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getOptionsLabel($locale)
    {
        return null !== $this->translations && $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getOptionsLabel()
            : '';
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
     * @param array $groupLabels indexed by rank and containing array of labels indexed by locale
     *                           Example : [
     *                           1 => ['fr' => 'French label 1', 'en' => 'English label 1'],
     *                           2 => ['fr' => 'French label 2', 'en' => 'English label 2'],
     *                           ]
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
     * @param array $groupOptions indexed by rank containing array of Products
     *                            Example : [
     *                            1 => [Product(), Product(), ...]
     *                            2 => [Product(), Product(), ...]
     *                            ]
     *
     * @return Package
     */
    public function setGroupsOptions(array $groupOptions)
    {
        $alreadyUsedOptions = [];

        foreach ($groupOptions as $rank => $options) {
            $options = (array) $options;

            /** @var Product $option */
            foreach ($options as $key => $option) {
                $optionId = $option->getId();

                if (isset($alreadyUsedOptions[$optionId])) {
                    unset($options[$key]);
                    continue;
                }

                $alreadyUsedOptions[$optionId] = true;
            }

            $options = array_values($options);

            if (!$this->groups->containsKey($rank)) {
                $this->groups->set($rank, new PackageGroup($this, $rank));
            }

            /** @var PackageGroup $packageGroup */
            $packageGroup = $this->groups->get($rank);
            $packageGroup->setOptions($options);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = [];

        foreach ($this->groups as $group) {
            $data[$group->getId()] = $group->getData();
        }

        return $data;
    }

    /**
     * @return string
     */
    public function serializeData()
    {
        return json_encode($this->getData());
    }

    /**
     * @param Product $product
     *
     * @return PackageGroup
     */
    public function getGroupOfProduct(Product $product)
    {
        foreach ($this->groups as $group) {
            if ($group->hasOption($product)) {
                return $group;
            }
        }

        return null;
    }

    public function isPlanningSelectable(): bool
    {
        return $this->planningSelectable;
    }

    public function canPlanningBeBought(): bool
    {
        return false === $this->participantWithPlanning && $this->planningSelectable;
    }

    public function setPlanningSelectable(bool $planningSelectable): void
    {
        $this->planningSelectable = $planningSelectable;
    }

    public function setParticipantWithPlanning(bool $participantWithPlanning): void
    {
        $this->participantWithPlanning = $participantWithPlanning;
    }

    public function isParticipantWithPlanning(): bool
    {
        return $this->participantWithPlanning;
    }
}
