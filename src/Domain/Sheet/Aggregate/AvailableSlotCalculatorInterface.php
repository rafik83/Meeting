<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet\Aggregate;

use Proximum\Vimeet\Domain\Model\Sheet;

interface AvailableSlotCalculatorInterface
{
    public function calculateAvailableSlotForSheet(Sheet $sheet, bool $indexSheet = true): void;
}
