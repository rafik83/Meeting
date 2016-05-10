<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
     * Nomenclature constructor.
     *
     * @param string $title
     * @param int    $depth
     * @param array  $value
     */
    public function __construct($title, $depth, array $value)
    {
        $this->title = $title;
        $this->depth = $depth;
        $this->value = $value;
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
     * @param string $locale
     *
     * @return array
     */
    public function getLabels($locale)
    {
        $labels = [];

        if (2 === $this->depth) {
            foreach ($this->getValue() as $item) {
                if (!isset($item['children'])) {
                    continue;
                }

                $labels[$item['label'][$locale]] = array_map(function ($value) use ($locale) {
                    return $value['label'][$locale];
                }, $item['children']);
            }

            return $labels;

        } elseif (1 === $this->depth) {
            $labels = array_map(function ($value) use ($locale) {
                return $value['label'][$locale];
            }, $this->getValue());

            return $labels;
        }

        return [];
    }
}
