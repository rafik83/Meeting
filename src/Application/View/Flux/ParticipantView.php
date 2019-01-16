<?php

namespace Proximum\Vimeet\Application\View\Flux;

class ParticipantView
{
    /** @var string */
    public $initial;

    /** @var string */
    public $function;

    /** @var \DateTimeInterface */
    public $registrationDate;

    /** @var SheetView */
    public $sheetView;

    public function __construct(
        string $initial,
        string $function,
        \DateTimeInterface $registrationDate,
        SheetView $sheetView
    ) {
        $this->initial = $initial;
        $this->function = $function;
        $this->registrationDate = $registrationDate;
        $this->sheetView = $sheetView;
    }
}
