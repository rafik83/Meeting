<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class NomenclatureItem
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $label;

    /**
     * @var NomenclatureItem[]
     */
    private $children;

    /**
     * @var NomenclatureItem
     */
    private $parent;

    /**
     * NomenclatureItem constructor.
     *
     * @param string             $key
     * @param string             $label
     * @param NomenclatureItem[] $children
     */
    public function __construct($key, $label, array $children = [])
    {
        $this->key      = $key;
        $this->label    = $label;
        $this->children = $children;

        foreach ($children as $child) {
            $child->setParent($this);
        }
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get parent
     *
     * @return NomenclatureItem
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent
     *
     * @param NomenclatureItem $parent
     *
     * @return NomenclatureItem
     */
    public function setParent(NomenclatureItem $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get children
     *
     * @return NomenclatureItem[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param string $locale
     *
     * @return NomenclatureItem[]
     */
    public function getChildrenSorted($locale)
    {
        $children = $this->getChildren();

        Nomenclature::sort($children, $locale);

        return $children;
    }

    /**
     * Get grant children
     *
     * @return NomenclatureItem[]
     */
    public function getGrantChildren()
    {
        return array_reduce($this->children, function (array $carry, NomenclatureItem $item) {
            return array_merge($carry, $item->getChildren());
        }, []);
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale)
    {
        return $this->label[$locale];
    }
}
