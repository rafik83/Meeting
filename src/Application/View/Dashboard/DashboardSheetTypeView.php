<?php

namespace Proximum\Vimeet\Application\View\Dashboard;

class DashboardSheetTypeView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $total;

    /**
     * @var string
     */
    public $title;

    /**
     * DashboardSheetTypeView constructor.
     *
     * @param int    $id
     * @param int    $total
     * @param string $title
     */
    public function __construct($id, $total, $title)
    {
        $this->id    = $id;
        $this->total = $total;
        $this->title = $title;
    }
}
