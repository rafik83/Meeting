<?php

namespace Proximum\Vimeet\Domain\Sheet\Aggregate;

use Proximum\Vimeet\Domain\Model\Sheet;

interface AvailableSlotCalculatorInterface
{
    public function calculateAvailableSlotForSheet(Sheet $sheet, bool $indexSheet = true): void;
}
