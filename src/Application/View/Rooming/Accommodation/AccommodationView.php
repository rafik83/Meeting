<?php

namespace Proximum\Vimeet\Application\View\Rooming\Accommodation;

class AccommodationView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var OvernightCapacityView[] */
    public $overnightCapacityViews;

    public function __construct(
        int $id,
        string $title
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->overnightCapacityViews = [];
    }

    public function addOvernightCapacityView(
        string $index,
        OvernightCapacityView $overnightCapacityView
    ): void {
        $this->overnightCapacityViews[$index] = $overnightCapacityView;
    }
}
