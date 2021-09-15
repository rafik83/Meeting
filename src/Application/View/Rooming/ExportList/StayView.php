<?php

namespace Proximum\Vimeet\Application\View\Rooming\ExportList;

class StayView
{
    /** @var string */
    public $accommodationTitle;

    /** @var string */
    public $arrivalDate;

    /** @var string */
    public $departureDate;

    /** @var string */
    public $roomType;

    /** @var string */
    public $roomNumber;

    /** @var UserSheetView[] */
    public $userSheetViews;

    public function __construct(
        string $accommodationTitle,
        string $arrivalDate,
        string $departureDate,
        string $roomType,
        string $roomNumber,
        array $userSheetViews
    ) {

        $this->accommodationTitle = $accommodationTitle;
        $this->arrivalDate = $arrivalDate;
        $this->departureDate = $departureDate;
        $this->roomType = $roomType;
        $this->roomNumber = $roomNumber;
        $this->userSheetViews = $userSheetViews;
    }
}
