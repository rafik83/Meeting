<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetCompleteness;

interface SheetCompletenessRepositoryInterface
{
    /**
     * @param SheetCompleteness $sheetCompleteness
     */
    public function add(SheetCompleteness $sheetCompleteness);

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return SheetCompleteness|null
     */
    public function findCompleteness(Sheet $sheet, $locale);

    /**
     * @param Sheet $sheet
     */
    public function removeForSheet(Sheet $sheet);
}
