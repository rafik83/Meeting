<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag as SheetTag;
use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;
use Proximum\Vimeet\Domain\Template\TranslatableInterface;

class Nomenclature extends EditableObject implements ContentObjectInterface, SearchableObjectInterface, IndexableObjectInterface, ExportableObjectInterface, TranslatableInterface
{
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
        $this->data['items'] = $items;

        return $this;
    }

    /**
     * @return string
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
        return isset($this->data['items']) ? $this->data['items'] : [];
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
    public function getContentValueLocalize($locale)
    {
        return $this->getContentValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLabel()
    {
        return $this->getNomenclatureLabel();
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
     * @param string $givenKey
     * @param string $locale
     *
     * @return string|null
     */
    public function getLabelForKey($givenKey, $locale = null)
    {
        foreach ($this->nomenclature->getLastLevel() as $key => $nomenclatureItem) {
            if ($nomenclatureItem->getKey() === $givenKey) {
                if (null !== $locale) {
                    return $nomenclatureItem->getLabel($locale);
                } else {
                    return $nomenclatureItem->getLabel($this->getLocale());
                }
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
     * @param null $locale
     *
     * @return array|null
     */
    public function getNomenclatureLabels($locale = null)
    {
        return $this->nomenclature->getLabels($locale ? : $this->locale);
    }

    /**
     * @return null|string
     */
    public function getNomenclatureLabel()
    {
        $labels = $this->getNomenclatureLabels();

        if (isset($labels[$this->getItem()])) {
            return $labels[$this->getItem()];
        }

        foreach ($labels as $values) {
            if (null !== $this->getItem()) {
                if (in_array($this->getItem(), $values, true)) {
                    return $this->getItem();
                } elseif (isset($values[$this->getItem()])) {
                    return $values[$this->getItem()];
                }
            }
        }

        return null;
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
        return $this->getMode() === self::MODE_SINGLES;
    }

    /**
     * @return bool
     */
    public function isRadios()
    {
        return $this->getMode() === self::MODE_RADIOS;
    }

    /**
     * @return bool
     */
    public function isCheckboxes()
    {
        return $this->getMode() === self::MODE_CHECKBOXES;
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
        return $this->getObjective() === self::OBJECTIVE_NEED;
    }

    /**
     * @return bool
     */
    public function isSupply()
    {
        return $this->getObjective() === self::OBJECTIVE_SUPPLY;
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
    public function getSearchableContent()
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

    /**
     * {@inheritdoc}
     */
    public function getExportableContent(array $taggedData = [], ?string $locale = null)
    {
        if (empty($this->getItems())) {
            return '';
        }

        $nomenclatureLabels = $this->getNomenclatureLabels();
        // Leaf elements are the ones with the longest path (max depth):
        $maxDepth = 1;
        $allItemPaths = [];

        foreach ($this->getItems() as $item) {
            $currentItemLabels = $this->getLabelsByItem($nomenclatureLabels, $item);
            $allItemPaths[] = $currentItemLabels;
            $currentItemLabelCount = count($currentItemLabels);
            // Update $maxDepth if current item's depth is greater than current $maxDepth:
            if ($currentItemLabelCount > $maxDepth) {
                $maxDepth = $currentItemLabelCount;
            }
        }

        // Filter items to keep only lowest-level ones (items with the max depth):
        $leaves = array_filter($allItemPaths, function ($item) use ($maxDepth) {
            return count($item) === $maxDepth;
        });

        // Implode inner content (each item's path):
        $leaves = array_map(function ($leave) {
            return implode('>', str_replace('>', '', $leave));
        }, $leaves);

        // Implode outer content (all item paths):
        return implode(";", $leaves);
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
            if (is_array($child)) {
                foreach ($child as $secondLevelKey => $secondLevelChild) {
                    if (is_array($secondLevelChild)) {
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
