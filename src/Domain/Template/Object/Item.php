<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

class Item
{
    /**
     * @var ItemCollection
     */
    private $collection;

    /**
     * @var string
     */
    private $title;

    /**
     * Item constructor.
     *
     * @param ItemCollection $collection
     * @param string         $title
     */
    public function __construct(ItemCollection $collection, $title)
    {
        $this->collection = $collection;
        $this->title      = $title;

        if ($this->collection->isTranslatable() && !is_array($this->title)) {
            $this->title = [$this->collection->getLocale() => $this->title];
        }
    }

    /**
     * Get collection
     *
     * @return ItemCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get title in the current locale
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->collection->isTranslatable() || is_array($this->title)) {
            return isset($this->title[$this->collection->getLocale()])
                ? $this->title[$this->collection->getLocale()]
                : null;
        }

        return $this->title;
    }

    /**
     * Get fallback title if object is translatable.
     *
     * @return string|null
     */
    public function getFallbackTitle()
    {
        if ($this->collection->isTranslatable() && is_array($this->title) || is_array($this->title)) {
            return isset($this->title[$this->collection->getFallback()])
                ? $this->title[$this->collection->getFallback()]
                : null;
        }

        return null;
    }

    /**
     * @param string $title
     *
     * @return Item
     */
    public function setTitle($title)
    {
        if ($this->collection->isTranslatable()) {
            if (!is_array($this->title)) {
                $this->title = [];
            }

            $this->title[$this->collection->getLocale()] = $title;
        } else {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return ['title' => $this->title];
    }
}
