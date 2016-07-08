<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;

class ExportData
{
    /**
     * @var Nomenclature
     */
    public $nomenclature;

    /**
     * @var string
     */
    public $charset;

    /**
     * ImportData constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }
}
