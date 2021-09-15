<?php

namespace Proximum\Vimeet\Domain\Spot;

use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\View\Spot\Import\SheetView;

class Import
{
    /** @var array */
    public $errorMessages;

    /** @var null|Spot */
    public $spot;

    /** @var int[] */
    public $sheetIds;

    /** @var SheetView[] */
    public $sheetViews;

    /**
     * @param Spot  $spot
     * @param int[] $sheetIds
     */
    public function __construct(?Spot $spot, array $sheetIds = [])
    {
        $this->spot = $spot;
        $this->sheetIds = $sheetIds;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return !empty($this->errorMessages);
    }

    /**
     * @param string $message
     */
    public function addError(string $message)
    {
        $this->errorMessages[] = $message;
    }

    /**
     * @param SheetView $sheetView
     */
    public function addSheetView(SheetView $sheetView)
    {
        $this->sheetViews[] = $sheetView;
    }
}
