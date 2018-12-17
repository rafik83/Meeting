<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class SheetView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $typeTitle;

    /** @var string|null */
    public $spotReference;

    public function __construct(
        int $id,
        string $title,
        string $typeTitle,
        ?string $spotReference
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->typeTitle = $typeTitle;
        $this->spotReference = $spotReference;
    }
}
