<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

use Proximum\Vimeet\Application\Components\Template\Exception\RowNotFoundException;

class Template
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $position;

    /**
     * @var Row[]
     */
    private $rows;

    /**
     * Template constructor.
     *
     * @param string $label
     * @param string $description
     * @param int    $position
     * @param Row[]  $rows
     */
    public function __construct($label, $description, $position, array $rows)
    {
        $this->label       = $label;
        $this->description = $description;
        $this->position    = $position;
        $this->rows        = $rows;
    }

    /**
     * Get label
     *
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale)
    {
        return is_array($this->label) ? (isset($this->label[$locale]) ? $this->label[$locale] : null) : $this->label;
    }

    /**
     * Get description
     *
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return is_array($this->description) ? (isset($this->description[$locale]) ? $this->description[$locale] : null) : $this->description;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get rows
     *
     * @return Row[]
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasRow($key)
    {
        return isset($this->rows[$key]);
    }

    /**
     * @param $key
     *
     * @return Row
     * @throws RowNotFoundException
     */
    public function getRow($key)
    {
        if ($this->hasRow($key)) {
            return $this->rows[$key];
        }

        throw new RowNotFoundException($key, array_keys($this->rows));
    }

    /**
     * @param string $tag
     * @param string $locale
     * @param array  $data
     *
     * @return array
     */
    public function getTaggedValues($tag, $locale, array $data)
    {
        // Filter tagged values
        $tagged = array_filter($data, function ($key) use ($tag) {
            return $this->hasRow($key) && $this->getRow($key)->hasTag($tag);
        }, ARRAY_FILTER_USE_KEY);

        // Get displayable values
        array_walk($tagged, function (&$value, $key) use ($locale) {
            $value = $this->getRow($key)->getDisplayableValue($value, $locale);
        });

        return $tagged;
    }

    /**
     * @param string $tag
     * @param string $locale
     * @param array  $data
     *
     * @return mixed
     */
    public function getTaggedValue($tag, $locale, array $data)
    {
        $values = array_values($this->getTaggedValues($tag, $locale, $data));

        return reset($values);
    }
}
