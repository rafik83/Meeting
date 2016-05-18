<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

use Proximum\Vimeet\Domain\Template\Object;
use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;

class Nomenclature extends EditableObject implements ContentObjectInterface
{
    /**
     * @var NomenclatureModel
     */
    private $nomenclature;

    /**
     * @var null|array
     */
    private $nomenclatureLabels;

    /**
     * @deprecated Use setItems instead
     *
     * @param string $nomenclature
     *
     * @return Nomenclature
     */
    public function setItem($nomenclature)
    {
        return $this->setItems($nomenclature);
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
     * @deprecated Use getItems instead
     *
     * @return string
     */
    public function getItem()
    {
        return $this->getItems();
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
    public function setContentValue($value)
    {
        $this->setItem($value);
    }

    /**
     * @return int
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
        $this->nomenclature       = $nomenclature;
        $this->nomenclatureLabels = $nomenclature->getLabels($this->locale) ? : [];
    }

    /**
     * @return array|null
     */
    public function getNomenclatureLabels()
    {
        return $this->nomenclatureLabels;
    }

    /**
     * @return nulL|string
     */
    public function getNomenclatureLabel()
    {
        if (isset($this->nomenclatureLabels[$this->getItem()])) {
            return $this->nomenclatureLabels[$this->getItem()];
        }

        foreach ($this->nomenclatureLabels as $values) {
            if (isset($values[$this->getItem()])) {
                return $values[$this->getItem()];
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return (bool) $this->getOption('multiple');
    }

    /**
     * @return bool
     */
    public function isExpanded()
    {
        return (bool) $this->getOption('expanded');
    }

    /**
     * @return bool
     */
    public function isSingles()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isRadios()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCheckboxes()
    {
        return true;
    }
}
