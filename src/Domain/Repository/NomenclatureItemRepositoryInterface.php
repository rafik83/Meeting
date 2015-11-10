<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\NomenclatureItemView;

interface NomenclatureItemRepositoryInterface
{
    /**
     * @param int    $nomenclatureId
     * @param string $locale
     *
     * @return NomenclatureItemView[]
     */
    public function getNomenclatureItemViewsByNomenclatureId($nomenclatureId, $locale);

    /**
     * @param int    $nomenclatureId
     * @param string $locale
     *
     * @return array
     */
    public function getArrayOfNomenclatureItemsByNomenclatureId($nomenclatureId, $locale);

    /**
     * @param int    $id
     * @param string $locale
     *
     * @return string
     */
    public function getNomenclatureItemLabelById($id, $locale);
}
