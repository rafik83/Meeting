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

class Nomenclature extends EditableObject
{
    /**
     * @var null|array
     */
    private $nomenclatureLabels;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getData();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->getNomenclatureLabel();
    }

    /**
     * @return int
     */
    public function getNomenclatureId()
    {
        return $this->getOption('nomenclature');
    }

    /**
     * @param array $nomenclatureLabels
     */
    public function setNomenclatureLabels(array $nomenclatureLabels)
    {
        $this->nomenclatureLabels = $nomenclatureLabels;
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
        if (isset($this->nomenclatureLabels[$this->getData()])) {
            return $this->nomenclatureLabels[$this->getData()];
        }

        foreach ($this->nomenclatureLabels as $values) {
            if (isset($values[$this->getData()])) {
                return $values[$this->getData()];
            }
        }

        return null;
    }
}
