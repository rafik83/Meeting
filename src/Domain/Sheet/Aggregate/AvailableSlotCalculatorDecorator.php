<?php

namespace Proximum\Vimeet\Domain\Sheet\Aggregate;

use Proximum\Vimeet\Domain\Model\Sheet;

class AvailableSlotCalculatorDecorator implements AvailableSlotCalculatorInterface
{
    /** @var AvailableSlotCalculator */
    private $calculator;

    /** @var int[] */
    private $processedSheets = [];

    public function __construct(AvailableSlotCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateAvailableSlotForSheet(Sheet $sheet, bool $indexSheet = true): void
    {
        if (isset($this->processedSheets[$sheet->getId()])) {
            return;
        }

        $this->calculator->calculateAvailableSlotForSheet($sheet, $indexSheet);

        $this->processedSheets[$sheet->getId()] = $sheet->getId();
    }
}
