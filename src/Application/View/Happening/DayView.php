<?php

namespace Proximum\Vimeet\Application\View\Happening;

class DayView
{
    /** @var int */
    private $scale;

    /** @var \DateTimeInterface */
    public $startTime;

    /** @var \DateTimeInterface */
    public $endTime;

    /** @var ProgramElementViewInterface[] */
    public $programElementViews;

    /** @var HappeningView[] */
    public $happenings;

    /**
     * @param \DateTimeInterface            $startTime
     * @param \DateTimeInterface            $endTime
     * @param int                           $scale
     * @param HappeningView[]               $happeningView
     * @param ProgramElementViewInterface[] $programElementViews
     */
    public function __construct(
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        $scale,
        array $happeningView,
        array $programElementViews = []
    ) {
        $this->startTime  = $startTime;
        $this->endTime    = $endTime;
        $this->scale      = $scale;
        $this->happenings = $happeningView;
        $this->programElementViews = $programElementViews;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDay()
    {
        return $this->startTime;
    }

    /**
     * @return string
     */
    public function getScale()
    {
        return gmdate('H:i', $this->scale * 60);
    }
}
