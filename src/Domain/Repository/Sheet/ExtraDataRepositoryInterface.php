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
use Proximum\Vimeet\Domain\Model\Sheet\ExtraData;

interface ExtraDataRepositoryInterface
{
    /**
     * @param ExtraData $extraData
     */
    public function add(ExtraData $extraData);

    /**
     * @param ExtraData $extraData
     */
    public function set(ExtraData $extraData);

    /**
     * @param Sheet  $sheet
     * @param string $name
     *
     * @return bool
     */
    public function hasExtraDataForSheet(Sheet $sheet, string $name): bool;
}
