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
     * @param string $nomenclature
     *
     * @return Nomenclature
     */
    public function setNomenclature($nomenclature)
    {
        $this->data['items'] = $nomenclature;

        return $this;
    }

    /**
     * @return string
     */
    public function getNomenclature()
    {
        return isset($this->data['items']) ? $this->data['items'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentValue()
    {
        return $this->getNomenclature() ? $this->getNomenclature() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function setContentValue($value)
    {
        $this->setNomenclature($value);
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
        if (isset($this->nomenclatureLabels[$this->getNomenclature()])) {
            return $this->nomenclatureLabels[$this->getNomenclature()];
        }

        foreach ($this->nomenclatureLabels as $values) {
            if (isset($values[$this->getNomenclature()])) {
                return $values[$this->getNomenclature()];
            }
        }

        return null;
    }
}
