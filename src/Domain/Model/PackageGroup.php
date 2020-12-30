<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class PackageGroup
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var int
     */
    private $rank;

    /**
     * @var ArrayCollection of PackageGroupTranslation
     */
    private $translations;

    /**
     * @var ArrayCollection of PackageOptionRank
     */
    private $optionRanks;

    /**
     * PackageGroup constructor.
     *
     * @param Package $package
     * @param int     $rank
     */
    public function __construct(Package $package, $rank)
    {
        $this->package      = $package;
        $this->rank         = $rank;
        $this->translations = new ArrayCollection();
        $this->optionRanks  = new ArrayCollection();
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
     * Get package
     *
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Get rank
     *
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set rank
     *
     * @param int $rank
     *
     * @return PackageGroup
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @param string $locale
     * @param string $label
     *
     * @return PackageGroup
     */
    public function translate($locale, $label)
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->set($label);
        } else {
            $this->translations->set($locale, new PackageGroupTranslation($this, $locale, $label));
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
        return $this->optionRanks->exists(function ($key, PackageOptionRank $rank) use ($option) {
            return $rank->getOption() === $option;
        });
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this
            ->translations
            ->map(function (PackageGroupTranslation $translation) {
                return $translation->getLabel();
            })
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
            ->optionRanks
            ->matching(Criteria::create()->orderBy(['rank' => Criteria::ASC]))
            ->map(function (PackageOptionRank $rank) { return $rank->getOption(); })
            ->toArray();
    }

    /**
     * Set ordered option
     *
     * @param Product[] $options indexed by rank
     *
     * @return PackageGroup
     */
    public function setOptions(array $options)
    {
        $alreadyUsedOptions = [];

        // Remove delete options and update rank
        /** @var PackageOptionRank $optionRank */
        foreach ($this->optionRanks as $optionRank) {
            $rank = array_search($optionRank->getOption(), $options);
            if (false === $rank) {
                $this->optionRanks->removeElement($optionRank);
            } else {
                $optionRank->setRank($rank);
            }

            $optionId = $optionRank->getOption()->getId();

            if (isset($alreadyUsedOptions[$optionId])) {
                // Remove duplicated option
                $this->optionRanks->removeElement($optionRank);
                continue;
            }

            $alreadyUsedOptions[$optionId] = true;
        }

        // Add new option
        foreach (array_values($options) as $rank => $option) {
            if (!$this->hasOption($option)) {
                $this->optionRanks->set($rank, new PackageOptionRank($this, $option, $rank));
            }
        }

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getLabel() : '';
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    protected function hasTranslation($locale): bool
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * @param string $locale
     *
     * @return PackageGroupTranslation
     */
    protected function getTranslation($locale)
    {
        return $this->translations->get($locale);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [
            'translations' => $this->getTranslationData(),
            'rank'         => $this->rank,
        ];
    }

    /**
     * @return string
     */
    public function serializeData(): string
    {
        return json_encode($this->getData());
    }

    /**
     * @return array
     */
    public function getTranslationData(): array
    {
        $data = [];

        foreach ($this->translations as $translation) {
            $data[$translation->getLocale()] = $translation->getData();
        }

        return $data;
    }

    /**
     * @return string
     */
    public function serializeTranslation(): string
    {
        return json_encode($this->getTranslationData());
    }
}
