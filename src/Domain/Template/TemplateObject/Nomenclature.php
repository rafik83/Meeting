<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag as SheetTag;
use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;
use Proximum\Vimeet\Domain\Template\TranslatableInterface;

class Nomenclature extends EditableObject implements ContentObjectInterface, SearchableObjectInterface, IndexableObjectInterface, ExportableObjectInterface, TranslatableInterface
{
    public const ITEMS = 'items';
    public const ITEM = 'item';
    public const SEMICOLON_ESCAPE_CHAR = '__VIMEET_SEMICOLON__';

    /**
     * Need and supply objectives constants
     */
    const OBJECTIVE_NONE   = 'none';
    const OBJECTIVE_SUPPLY = 'supply';
    const OBJECTIVE_NEED   = 'need';

    /**
     * Singles, checkboxes and radios display mode
     */
    const MODE_SINGLES    = 'singles';
    const MODE_CHECKBOXES = 'checkboxes';
    const MODE_RADIOS     = 'radios';

    /**
     * @var NomenclatureModel
     */
    private $nomenclature;

    /**
     * @param string $nomenclature
     *
     * @return Nomenclature
     */
    public function setItem($nomenclature)
    {
        return $this->setItems((array) $nomenclature);
    }

    /**
     * @param array $items
     *
     * @return Nomenclature
     */
    public function setItems(array $items)
    {
        $this->data[self::ITEMS] = $items;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getItem()
    {
        return !empty($this->getItems()) ? array_values($this->getItems())[0] : null;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->data[self::ITEMS] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getItem() ? $this->getItem() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValueLocalize($locale = null)
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLabel()
    {
        return implode(', ', $this->getNomenclatureLabelOfItems());
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setItem($value);
    }

    /**
     * @param string $givenLabel
     * @param string $locale
     *
     * @return string|null
     */
    public function getKeyForLabel($givenLabel, $locale = null)
    {
        foreach ($this->nomenclature->getLastLevel() as $key => $nomenclatureItem) {
            if (null !== $locale) {
                if ($nomenclatureItem->getLabel($locale) === $givenLabel) {
                    return $nomenclatureItem->getKey();
                }
            } else {
                if ($nomenclatureItem->getLabel($this->getLocale()) === $givenLabel) {
                    return $nomenclatureItem->getKey();
                }
            }
        }

        return null;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        $lastLevel = $this->nomenclature->getLastLevel();

        return isset($lastLevel[$key]);
    }

    /**
     * @param string $givenKey
     * @param string $locale
     *
     * @return string|null
     */
    public function getLabelForKey($givenKey, $locale = null)
    {
        $locale = $locale ?? $this->getLocale();

        foreach ($this->nomenclature->getLastLevel() as $key => $nomenclatureItem) {
            if ($nomenclatureItem->getKey() === $givenKey) {
                return $nomenclatureItem->getLabel($locale);
            }
        }

        return null;
    }

    /**
     * @return int|string
     */
    public function getNomenclatureId()
    {
        return $this->getOption('nomenclature');
    }

    /**
     * @param NomenclatureModel $nomenclature
     */
    public function setNomenclature(NomenclatureModel $nomenclature)
    {
        $this->nomenclature = $nomenclature;
        $this->setOption('nomenclature', $nomenclature->getId());
    }

    /**
     * @return NomenclatureModel
     */
    public function getNomenclatureModel()
    {
        return $this->nomenclature;
    }

    /**
     * Get nomenclature
     *
     * @return int
     */
    public function getNomenclature()
    {
        return $this->getOption('nomenclature');
    }

    /**
     * Get the labels of all the items
     *
     * @param null $locale
     *
     * @return array|null
     */
    public function getNomenclatureLabels($locale = null)
    {
        return $this->nomenclature->getLabels($locale ?: $this->locale);
    }

    /**
     * Get the keys of all the items
     *
     * @return array|null
     */
    public function getNomenclatureKeys(): ?array
    {
        return $this->nomenclature->getTreeKeys();
    }

    /**
     * @deprecated use getNomenclatureLabelOfItems with implode
     *
     * @return null|string
     */
    public function getNomenclatureLabel()
    {
        return $this->getLabelForKey($this->getItem());
    }

    /**
     * @return string[]
     */
    public function getNomenclatureLabelOfItems(): array
    {
        $labels = [];

        foreach ($this->getItems() as $item) {
            $labels[] = $this->getLabelForKey($item);
        }

        return $labels;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->getOption('mode');
    }

    /**
     * @return bool
     */
    public function isSingles()
    {
        return self::MODE_SINGLES === $this->getMode();
    }

    /**
     * @return bool
     */
    public function isRadios()
    {
        return self::MODE_RADIOS === $this->getMode();
    }

    /**
     * @deprecated see isMultiple
     *
     * @return bool
     */
    public function isCheckboxes(): bool
    {
        return $this->isMultiple();
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return self::MODE_CHECKBOXES === $this->getMode();
    }

    /**
     * @return string
     */
    public function getObjective()
    {
        return null !== $this->getOption('objective') ? $this->getOption('objective') : self::OBJECTIVE_NONE;
    }

    /**
     * @return bool
     */
    public function isNeed()
    {
        return self::OBJECTIVE_NEED === $this->getObjective();
    }

    /**
     * @return bool
     */
    public function isSupply()
    {
        return self::OBJECTIVE_SUPPLY === $this->getObjective();
    }

    /**
     * @return bool
     */
    public function isDisplayOnCompanyProfile()
    {
        $tags = $this->getTags();

        return !in_array(SheetTag::PARTICIPANT_POSITION, $tags)
            && !in_array(SheetTag::SHEET_ORGANIZATION_STAFF, $tags)
            && !in_array(SheetTag::SHEET_ORGANIZATION_TURNOVER, $tags)
        ;
    }

    /**
     * @return array
     */
    public function getLabelsOfAllSelectedLevels()
    {
        $nomenclatureLabels = $this->getNomenclatureLabels();

        $labels = [];

        foreach ($this->getItems() as $item) {
            foreach ($this->getLabelsByItem($nomenclatureLabels, $item) as $label) {
                $labels[] = $label;
            }
        }

        return array_unique($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableContent(?string $locale = null)
    {
        return $this->getLabelsOfAllSelectedLevels();
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return (bool) $this->getOption('required');
    }

    /**
     * {@inheritdoc}
     */
    public function getExportableFieldname($locale, $fallback)
    {
        return $this->getLabel($locale, $fallback);
    }

    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        return $this->getNomenclatureItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getNomenclatureItems(bool $displayNomenclatureIds = false)
    {
        if (empty($this->getItems())) {
            return '';
        }

        $nomenclatureLabels = $displayNomenclatureIds ? $this->getNomenclatureKeys() : $this->getNomenclatureLabels();

        // Leaf elements are the ones with the longest path (max depth):
        $maxDepth = 1;
        $allItemPaths = [];

        foreach ($this->getItems() as $item) {
            if(\is_array($item)) {
                $currentItemLabels = [
                    implode(
                        ', ',
                        array_map(
                            function ($subItem) {
                                return $this->getLabelForKey($subItem);
                            },
                            $item
                        )
                    ),
                ];
            } else {
                $currentItemLabels = $this->getLabelsByItem($nomenclatureLabels, $item);
            }

            $allItemPaths[] = $currentItemLabels;
            $currentItemLabelCount = \count($currentItemLabels);
            // Update $maxDepth if current item's depth is greater than current $maxDepth:
            if ($currentItemLabelCount > $maxDepth) {
                $maxDepth = $currentItemLabelCount;
            }
        }

        // Filter items to keep only lowest-level ones (items with the max depth):
        $leaves = array_filter($allItemPaths, static function ($item) use ($maxDepth) {
            return \count($item) === $maxDepth;
        });

        // Implode inner content (each item's path):
        $leaves = array_map(static function ($leave) {
            return implode('>', str_replace('>', '', $leave));
        }, $leaves);

        // Implode outer content (all item paths):
        return implode(';', $leaves);
    }

    /**
     * @param array  $nomenclatureLabels
     * @param string $item
     *
     * @return array
     */
    private function getLabelsByItem(&$nomenclatureLabels, $item)
    {
        foreach ($nomenclatureLabels as $firstLevelKey => $child) {
            if (\is_array($child)) {
                foreach ($child as $secondLevelKey => $secondLevelChild) {
                    if (\is_array($secondLevelChild)) {
                        foreach ($secondLevelChild as $lastLevelKey => $lastLevelLabel) {
                            if ($lastLevelKey === $item) {
                                return [$firstLevelKey, $secondLevelKey, $lastLevelLabel];
                            }
                        }
                    } elseif ($secondLevelKey === $item) {
                        return [$firstLevelKey, $secondLevelChild];
                    }
                }
            } elseif ($firstLevelKey === $item) {
                return [$child];
            }
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations(array $locales = [])
    {
        $translations = [];

        foreach ($this->getItems() as $itemKey) {
            foreach ($locales as $locale) {
                $translations[$locale] = $this->getLabelForKey($itemKey, $locale);
            }
        }

        return $translations;
    }
}
