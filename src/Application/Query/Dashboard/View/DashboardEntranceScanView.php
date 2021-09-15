<?php

namespace Proximum\Vimeet\Application\Query\Dashboard\View;

class DashboardEntranceScanView
{
    /** @var bool */
    private $enabled;

    /** @var int */
    private $visitorsTotal;

    /** @var int */
    private $uniqueVisitorsTotal;

    /** @var int[] */
    private $uniqueVisitorsByType;

    /** @var array */
    private $visitorsByTypeAndDay;

    /** @var \DateTimeInterface[] */
    private $formattedDays;

    /**
     * @param bool                 $enabled
     * @param \DateTimeInterface[] $formattedDays
     * @param int                  $visitorsTotal
     * @param int                  $uniqueVisitorsTotal
     * @param int[]                $uniqueVisitorsByType
     * @param array                $visitorsByTypeAndDay
     */
    public function __construct(
        bool $enabled,
        array $formattedDays = [],
        int $visitorsTotal = 0,
        int $uniqueVisitorsTotal = 0,
        array $uniqueVisitorsByType = [],
        array $visitorsByTypeAndDay = []
    ) {
        $this->formattedDays = $formattedDays;
        $this->visitorsTotal = $visitorsTotal;
        $this->uniqueVisitorsTotal = $uniqueVisitorsTotal;
        $this->uniqueVisitorsByType = $uniqueVisitorsByType;
        $this->visitorsByTypeAndDay = $visitorsByTypeAndDay;
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getVisitorsTotal(): int
    {
        return $this->visitorsTotal;
    }

    public function getUniqueVisitorsTotal(): int
    {
        return $this->uniqueVisitorsTotal;
    }

    public function getUniqueVisitorsByType(int $typeId): int
    {
        return $this->uniqueVisitorsByType[$typeId] ?? 0;
    }

    public function getVisitorsByTypeAndDay(int $typeId, string $day): int
    {
        return $this->visitorsByTypeAndDay[$typeId][$day] ?? 0;
    }

    /**
     * @return \DateTimeInterface[]
     */
    public function getFormattedDays(): array
    {
        return $this->formattedDays;
    }

    public function getTotalVisitorsByDay(string $day): int
    {
        $totalVisitorsByDay = 0;

        foreach ($this->visitorsByTypeAndDay as $visitorsByDay) {
            $totalVisitorsByDay += $visitorsByDay[$day] ?? 0;
        }

        return $totalVisitorsByDay;
    }

    public function getVisitorsByType(int $typeId): int
    {
        if (!isset($this->visitorsByTypeAndDay[$typeId])) {
            return 0;
        }

        $totalVisitorsByType = 0;

        foreach ($this->visitorsByTypeAndDay[$typeId] as $visitorsByDay) {
            $totalVisitorsByType += $visitorsByDay;
        }

        return $totalVisitorsByType;
    }
}
