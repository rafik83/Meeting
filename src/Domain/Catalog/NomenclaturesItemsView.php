<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\NomenclatureItem;

class NomenclaturesItemsView
{
    /** @var array|NomenclatureItem[] */
    public $nomenclaturesItems;

    /** @var int */
    public $maxDepth;

    /**
     * @var NomenclatureItem[] $nomenclaturesItems
     * @param int              $maxDepth
     */
    public function __construct(array $nomenclaturesItems, int $maxDepth)
    {
        $this->nomenclaturesItems = $nomenclaturesItems;
        $this->maxDepth = $maxDepth;
    }

}
