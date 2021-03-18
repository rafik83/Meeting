<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

use DateTimeInterface;

class HappeningDayView
{
    public DateTimeInterface $happeningDay;

    public array $happeningListView;

    public function __construct(DateTimeInterface $happeningDay, array $happeningListView)
    {
        $this->happeningDay = $happeningDay;
        $this->happeningListView = $happeningListView;
    }
}
