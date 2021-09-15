<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event\Day;

class GroupView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var SheetView[] */
    public $sheetViews;

    /** @var Day[] */
    public $days;

    /**
     * @param int         $id
     * @param string      $title
     * @param SheetView[] $sheetViews
     * @param Day[]       $days
     */
    public function __construct($id, $title, array $sheetViews, array $days)
    {
        $this->id         = $id;
        $this->title      = $title;
        $this->sheetViews = $sheetViews;
        $this->days       = $days;
    }
}
