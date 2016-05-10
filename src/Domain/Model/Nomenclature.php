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
    }
}
