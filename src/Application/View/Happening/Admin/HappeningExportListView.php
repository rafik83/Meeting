<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

class HappeningExportListView
{
    /** @var HappeningExportView[] */
    private $happeningExportListView;

    public function __construct(array $happeningExportListView)
    {
        $this->happeningExportListView = $happeningExportListView;
    }

    /**
     * @return HappeningExportView[]
     */
    public function getHappeningExportListView(): array
    {
        return $this->happeningExportListView;
    }
}
