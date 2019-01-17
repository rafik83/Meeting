<?php

namespace Proximum\Vimeet\Application\View\Flux;

class ParticipantView
{
    /** @var string */
    public $initials;

    /** @var string */
    public $position;

    /** @var \DateTimeInterface */
    public $registrationDate;

    /** @var SheetView */
    public $sheetView;

    public function __construct(
        string $initials,
        string $position,
        \DateTimeInterface $registrationDate,
        SheetView $sheetView
    ) {
        $this->initials = $initials;
        $this->position = $position;
        $this->registrationDate = $registrationDate;
        $this->sheetView = $sheetView;
    }
}
