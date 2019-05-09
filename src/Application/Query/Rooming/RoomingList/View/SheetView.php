<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class SheetView
{
    /** @var int */
    public $id;

    /** @var null|string */
    public $title;

    /** @var string */
    public $typeTitle;

    /** @var string|null */
    public $spotReference;

    /** @var string */
    public $sheetState;

    public function __construct(
        int $id,
        ?string $title,
        string $typeTitle,
        ?string $spotReference,
        string $sheetState
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->typeTitle = $typeTitle;
        $this->spotReference = $spotReference;
        $this->sheetState = $sheetState;
    }
}
