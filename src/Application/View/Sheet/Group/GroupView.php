<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group;

class GroupView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var SheetView[] */
    public $sheetViews;

    /**
     * @param int    $id
     * @param string $title
     * @param array  SheetView[]
     */
    public function __construct($id, $title, array $sheetViews)
    {
        $this->id = $id;
        $this->title = $title;
        $this->sheetViews = $sheetViews;
    }
}
