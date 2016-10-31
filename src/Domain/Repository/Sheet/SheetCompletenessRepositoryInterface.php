<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
     * @param SheetCompleteness $sheetCompleteness
     */
    public function set(SheetCompleteness $sheetCompleteness);

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
