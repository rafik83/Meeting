<?php

namespace Proximum\Vimeet\Application\View\Flux;

class ParticipantView
{
    /** @var string */
    public $initial;

    /** @var string */
    public $position;

    /** @var \DateTimeInterface */
    public $registrationDate;

    /** @var SheetView */
    public $sheetView;

    public function __construct(
        string $initial,
        string $position,
        \DateTimeInterface $registrationDate,
        SheetView $sheetView
    ) {
        $this->initial = $initial;
        $this->position = $position;
        $this->registrationDate = $registrationDate;
        $this->sheetView = $sheetView;
    }
}
