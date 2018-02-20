<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetIndexerInterface
{
    /**
     * @param Sheet[] $sheets
     */
    public function reindexSheets(array $sheets): void;

    /**
     * @param Sheet[] $sheets
     */
    public function updateSheets(array $sheets): void;
}
