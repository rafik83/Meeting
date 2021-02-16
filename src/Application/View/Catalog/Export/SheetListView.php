<?php

namespace Proximum\Vimeet\Application\View\Catalog\Export;

class SheetListView
{
    /** @var array */
    public $sheetViews;

    /** @var array */
    public $registrationFields;

    /** @var array */
    public $sheetFields;

    /** @var string */
    public $typeOrCategoryColumn;

    /** @var bool */
    public $isTypeColumn;

    /** @var string */
    public $participantPositionColumn;

    /**
     * @param array  $sheetViews
     * @param array  $registrationFields
     * @param array  $sheetFields
     * @param string $participantPositionColumn
     * @param string $typeOrCategoryColumn
     * @param bool   $isTypeColumn
     */
    public function __construct(
        array $sheetViews = [],
        array $registrationFields = [],
        array $sheetFields = [],
        string $participantPositionColumn,
        string $typeOrCategoryColumn,
        bool $isTypeColumn = true
    ) {
        $this->sheetViews = $sheetViews;
        $this->registrationFields = $registrationFields;
        $this->sheetFields = $sheetFields;
        $this->participantPositionColumn = $participantPositionColumn;
        $this->typeOrCategoryColumn = $typeOrCategoryColumn;
        $this->isTypeColumn = $isTypeColumn;
    }
}
