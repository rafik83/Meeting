<?php

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Nomenclature".
 */
class Nomenclature
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $depth = 1;

    /**
     * @var array
     */
    private $value = [];

    /**
     * @var bool
     */
    private $sort = true;

    /**
     * @var Event|null
     */
    private $event;

    /**
     * The nomenclature from this nomenclature has been clone
     *
     * @var Nomenclature
     */
    private $original;

    /**
     * Nomenclature constructor.
     *
     * @param string       $title
     * @param int          $depth
     * @param array        $value
     * @param bool         $sort
     * @param Event        $event
     * @param Nomenclature $original
     */
    public function __construct($title, $depth = 1, array $value = [], $sort = true, Event $event = null, Nomenclature $original = null)
    {
        $this->title    = $title;
        $this->depth    = $depth;
        $this->value    = $value;
        $this->sort     = $sort;
        $this->event    = $event;
        $this->original = $original;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * Get depth
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Get value
     *
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get label from $key
     *
     * @param string $key
     * @param string $locale
     * @param string $fallback
     *
     * @return string
     */
    public function getLabel($key, $locale, $fallback = null)
    {
        return ($found = self::find($this->value, $key)) ? self::label($found, $locale, $fallback) : null;
    }

    /**
     * Get item from $key
     *
     * @param array  $array
     * @param string $key
     *
     * @return array
     */
    private static function find(&$array, $key)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach ($array as $child) {
            if (isset($child['children']) && $found = self::find($child['children'], $key)) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @param array  $value
     * @param string $locale
     * @param string $fallback
     *
     * @return string
     */
    private static function label(&$value, $locale, $fallback = null)
    {
        return isset($value['label'][$locale]) ?
            $value['label'][$locale] :
            ($fallback ? self::label($value, $fallback) : null);
    }

    /**
     * @return NomenclatureItem[]
     */
    public function getItems()
    {
        return self::items($this->value, $this->sort);
    }

    /**
     * @return NomenclatureItem[]
     */
    public function getChildren()
    {
        return $this->getItems();
    }

    /**
     * @param $locale
     *
     * @return NomenclatureItem[]
     */
    public function getChildrenSorted($locale)
    {
        $children = $this->getChildren();

        if ($this->sort) {
            self::sort($children, $locale);
        }

        return $children;
    }

    /**
     * @param array $items
     * @param bool  $sort
     *
     * @return NomenclatureItem[]
     */
    private static function items(array $items, $sort)
    {
        return array_map(function ($key, $item) use ($sort) {
            return new NomenclatureItem(
                (string) $key,
                isset($item['label']) ? $item['label'] : [],
                isset($item['children']) ? self::items($item['children'], $sort) : [],
                $sort
            );
        }, array_keys($items), $items);
    }

    /**
     * @return NomenclatureItem[]
     */
    public function getFirstLevel()
    {
        return $this->getItems();
    }

    /**
     * @param $locale
     *
     * @return NomenclatureItem[]
     */
    public function getFirstLevelSorted($locale)
    {
        $items = $this->getItems();

        if ($this->sort) {
            self::sort($items, $locale);
        }

        return $items;
    }

    /**
     * @param array  $items
     * @param string $locale
     */
    public static function sort(&$items, $locale)
    {
        usort($items, function (NomenclatureItem $one, NomenclatureItem $another) use ($locale) {
            return strcasecmp($one->getLabel($locale), $another->getLabel($locale));
        });
    }

    /**
     * @return NomenclatureItem[]
     */
    public function getSecondLevel()
    {
        return array_reduce($this->getItems(), function (array $carry, NomenclatureItem $item) {
            return array_merge($carry, $item->getChildren());
        }, []);
    }

    /**
     * @return NomenclatureItem[]
     */
    public function getThirdLevel()
    {
        return array_reduce($this->getItems(), function (array $carry, NomenclatureItem $item) {
            return array_merge($carry, $item->getGrandChildren());
        }, []);
    }

    /**
     * @return NomenclatureItem[] indexed by key
     */
    public function getLastLevel()
    {
        $nomenclatureItems = [];

        if (1 === $this->depth) {
            $nomenclatureItems = $this->getFirstLevel();
        }

        if (2 === $this->depth) {
            $nomenclatureItems = $this->getSecondLevel();
        }

        if (3 === $this->depth) {
            $nomenclatureItems = $this->getThirdLevel();
        }

        $nomenclatureItemsIndexedByKey = [];

        foreach ($nomenclatureItems as $nomenclatureItem) {
            $nomenclatureItemsIndexedByKey[$nomenclatureItem->getKey()] = $nomenclatureItem;
        }

        return $nomenclatureItemsIndexedByKey;
    }

    /**
     * @return array
     */
    public function getTreeKeys(): array
    {
        $labels = [];

        if (3 === $this->depth) {
            foreach ($this->getValue() as $key => $item) {
                if (!isset($item['children'])) {
                    continue;
                }

                foreach ($item['children'] as $keyChild => $secondDepth) {
                    foreach (array_keys($secondDepth['children']) as $id) {
                        $labels[$key][$keyChild][$id] = $id;
                    }
                }
            }

            return $labels;
        } elseif (2 === $this->depth) {
            foreach ($this->getValue() as $keyTwo => $item) {
                if (!isset($item['children'])) {
                    continue;
                }

                foreach ($item['children'] as $idTwo => $tab) {
                    $labels[$keyTwo][$idTwo] = $idTwo;
                }
            }

            return $labels;
        } elseif (1 === $this->depth) {

            foreach ($this->getValue() as $idOne => $val) {
                $labels[$idOne] = $idOne;
            }

            return $labels;
        }

        return [];
    }

    /**
     * @param string $locale
     *
     * @return array
     */
    public function getLabels($locale): array
    {
        $treeKeys = $this->getTreeKeys();

        return $this->transformDataToLabel($treeKeys, $locale);
    }

    /**
     * @param array $keys
     *
     * @return bool
     */
    public function any(array $keys)
    {
        return !empty(array_filter($this->getChildren(), function (NomenclatureItem $item) use ($keys) {
            return in_array($item->getKey(), $keys) || $item->any($keys);
        }));
    }

    /**
     * @return Nomenclature
     */
    public function enableSort()
    {
        $this->sort = true;

        return $this;
    }

    /**
     * @return Nomenclature
     */
    public function disableSort()
    {
        $this->sort = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSorted()
    {
        return $this->sort;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Nomenclature
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get locales available in the nomenclature
     *
     * @return array
     */
    public function getLocales()
    {
        return array_unique(array_reduce($this->getChildren(), function (array $carry, NomenclatureItem $item) {
            return array_merge($carry, $item->getLocales());
        }, []));
    }

    /**
     * Set value
     *
     * @param int   $depth
     * @param array $value
     *
     * @return Nomenclature
     */
    public function update($depth, $value)
    {
        $this->depth = $depth;
        $this->value = $value;

        return $this;
    }

    /**
     * Get event
     *
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * Get original
     *
     * @return Nomenclature
     */
    public function getOriginal(): ?Nomenclature
    {
        return $this->original;
    }

    /**
     * Count the number of items per column
     *
     * @param string $locale
     *
     * @return array
     */
    public function getLevelsArchitecture($locale)
    {
        $levelsArchitecture = [];

        if ($this->depth >= 2) {
            if (2 === $this->depth) {
                $levelsArchitecture = $this->buildArchitectureLevel($this, $levelsArchitecture, $locale, null);
            } else {
                foreach ($this->getFirstLevelSorted($locale) as $firstLevelKey => $firstLevel) {
                    $levelsArchitecture = $this->buildArchitectureLevel($firstLevel, $levelsArchitecture, $locale, $firstLevelKey);
                }
            }
        }

        return $levelsArchitecture;
    }

    /**
     * @param Nomenclature|NomenclatureItem $currentLevel
     * @param array                         $levelsArchitecture
     * @param string                        $locale
     * @param int|null                      $firstLevelKey
     *
     * @return array
     */
    private function buildArchitectureLevel($currentLevel, $levelsArchitecture, $locale, $firstLevelKey = null)
    {
        $itemsByColumn  = [
            1 => [
                'items'        => 0,
                'secondLevels' => 0,
            ],
            2 => [
                'items'        => 0,
                'secondLevels' => 0,
            ],
            3 => [
                'items'        => 0,
                'secondLevels' => 0,
            ],
        ];

        if (null !== $firstLevelKey) {
            $levelsArchitecture[$firstLevelKey] = [];
            $elementToAssign                    = $levelsArchitecture[$firstLevelKey];
        } else {
            $elementToAssign = $levelsArchitecture;
        }

        $currentColumn = 1;

        if ($currentLevel instanceof NomenclatureItem) {
            $totalItems = count($currentLevel->getGrandChildren());
        } else {
            $totalItems = count($this->getLastLevel());
        }

        $numberByColumn = $totalItems / 3;

        $elementToAssign['elements']        = $itemsByColumn;
        $elementToAssign['numberOfColumns'] = 3;

        foreach ($currentLevel->getChildrenSorted($locale) as $secondLevel) {
            $numberOfChildren = count($secondLevel->getChildrenSorted($locale));

            if ($elementToAssign['elements'][$currentColumn]['items'] > $numberByColumn) {
                ++$currentColumn;
            }

            $elementToAssign['elements'][$currentColumn]['items'] += $numberOfChildren;
            ++$elementToAssign['elements'][$currentColumn]['secondLevels'];
        }

        $elementToAssign['elements'][2]['secondLevels'] += $elementToAssign['elements'][1]['secondLevels'];
        $elementToAssign['elements'][3]['secondLevels'] += $elementToAssign['elements'][2]['secondLevels'];

        if ($elementToAssign['elements'][3]['items'] === 0) {
            $elementToAssign['numberOfColumns'] = 2;
        }
        if ($elementToAssign['elements'][2]['items'] === 0) {
            $elementToAssign['numberOfColumns'] = 1;
        }

        if (null !== $firstLevelKey) {
            $levelsArchitecture[$firstLevelKey] = $elementToAssign;
        } else {
            $levelsArchitecture = $elementToAssign;
        }

        return $levelsArchitecture;
    }

    /**
     * @param array|string $data
     * @param string       $locale
     *
     * @return array|string
     */
    private function transformDataToLabel($data, string $locale)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $result = $this->transformDataToLabel($value, $locale);

                if (is_array($result)) {
                    unset($data[$key]);
                    $key = $this->getLabel($key, $locale);
                }

                $data[$key] = $result;
            }

            return $data;
        }

        return $this->getLabel($data, $locale);
    }
}
