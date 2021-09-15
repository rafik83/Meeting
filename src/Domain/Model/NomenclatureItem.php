<?php

namespace Proximum\Vimeet\Domain\Model;

class NomenclatureItem
{
    /**
     * @var mixed|string
     */
    private $key;

    /**
     * @var array label indexed by locale
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
     * @var bool
     */
    private $sort = true;

    /**
     * NomenclatureItem constructor.
     *
     * @param string             $key
     * @param array              $label    indexed by locale
     * @param NomenclatureItem[] $children
     * @param bool               $sort
     */
    public function __construct($key, array $label, array $children = [], $sort = true)
    {
        $this->key      = $key;
        $this->label    = $label;
        $this->children = $children;
        $this->sort     = $sort;

        foreach ($children as $child) {
            $child->setParent($this);
        }
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey(): string
    {
        return (string) $this->key;
    }

    public function getCleanKey(): string
    {
        return str_replace('.', '_', $this->getKey());
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

        if ($this->sort) {
            Nomenclature::sort($children, $locale);
        }

        return $children;
    }

    /**
     * Get grant children
     *
     * @return NomenclatureItem[]
     */
    public function getGrandChildren()
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
        return isset($this->label[$locale]) ? $this->label[$locale] : null;
    }

    /**
     * @param array $keys
     *
     * @return bool
     */
    public function any(array $keys)
    {
        return !empty(array_filter($this->children, function (NomenclatureItem $child) use ($keys) {
            return in_array($child->getKey(), $keys) || $child->any($keys);
        }));
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        if (!is_array($this->label)) {
            return [];
        }

        return array_unique(array_merge(
            array_keys($this->label),
            array_reduce($this->getChildren(), function (array $carry, NomenclatureItem $item) {
                return array_merge($carry, $item->getLocales());
            }, [])
        ));
    }
}
