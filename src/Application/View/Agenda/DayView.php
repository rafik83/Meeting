<?php

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class DayView
{
    private const SLOT_DATE_HOUR_INDEX = 'Y-m-d H:i:s';

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var int */
    public $scale;

    /** @var HappeningView[] */
    public $happenings;

    /** @var UnavailabilityView[] */
    public $unavailabilities;

    /** @var MassUnavailabilityView[] */
    public $masses;

    /** @var MeetingView[] */
    public $meetings;

    /** @var TimeRangeInterface[] */
    public $agendaSlots;

    /** @var CancelAttendanceUnavailabilityView|null */
    public $cancelAttendanceUnavailabilityView;

    /** @var AvailableSlotView[] */
    public $availableSlotViews;

    /** @var bool */
    public $isUnavailableForThisDay;

    /** @var MeetingView[] */
    private $meetingViewsByDateBegin;

    /** @var MassUnavailabilityView[] */
    private $massUnavailabilityViewsByDateBegin;

    /**
     * @param \DateTimeInterface                      $begin
     * @param \DateTimeInterface                      $end
     * @param int                                     $scale
     * @param HappeningView[]                         $happenings
     * @param UnavailabilityView[]                    $unavailabilities
     * @param MassUnavailabilityView[]                $masses
     * @param MeetingView[]                           $meetings
     * @param TimeRangeInterface[]                    $agendaSlots
     * @param array                                   $availableSlotViews
     * @param CancelAttendanceUnavailabilityView|null $cancelAttendanceUnavailabilityView
     * @param bool                                    $isUnavailableForThisDay
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $scale,
        array $happenings,
        array $unavailabilities,
        array $masses,
        array $meetings,
        array $agendaSlots,
        array $availableSlotViews,
        ?CancelAttendanceUnavailabilityView $cancelAttendanceUnavailabilityView = null,
        $isUnavailableForThisDay = false
    ) {
        $this->begin = $begin;
        $this->end = $end;
        $this->scale = $scale;
        $this->happenings = $happenings;
        $this->unavailabilities = $unavailabilities;
        $this->masses = $masses;
        $this->meetings = $meetings;
        $this->cancelAttendanceUnavailabilityView = $cancelAttendanceUnavailabilityView;
        $this->availableSlotViews = $availableSlotViews;
        $this->isUnavailableForThisDay = $isUnavailableForThisDay;
        $this->agendaSlots = [];
        $this->meetingViewsByDateBegin = [];
        $this->massUnavailabilityViewsByDateBegin = [];

        if (empty($agendaSlots)) {
            return;
        }

        foreach ($agendaSlots as $agendaSlot) {
            $index = $agendaSlot->getBegin()->format(self::SLOT_DATE_HOUR_INDEX);
            $this->agendaSlots[$index] = $agendaSlot;
        }

        foreach ($meetings as $meeting) {
            $index = $meeting->getBegin()->format(self::SLOT_DATE_HOUR_INDEX);

            if (!isset($this->meetingViewsByDateBegin[$index])) {
                $this->meetingViewsByDateBegin[$index] = [];
            }

            $this->meetingViewsByDateBegin[$index][] = $meeting;
        }

        foreach ($masses as $mass) {
            $index = $mass->getBegin()->format(self::SLOT_DATE_HOUR_INDEX);
            $this->agendaSlots[$index] = new TimeRangeView($mass->getBegin(), $mass->getEnd());

            if (!isset($this->massUnavailabilityViewsByDateBegin[$index])) {
                $this->massUnavailabilityViewsByDateBegin[$index] = [];
            }

            $this->massUnavailabilityViewsByDateBegin[$index][] = $mass;
        }

        ksort($this->agendaSlots);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDay(): \DateTimeInterface
    {
        return $this->begin;
    }

    /**
     * @return string
     */
    public function getScale(): string
    {
        return gmdate('H:i', $this->scale * 60);
    }

    /**
     * @return array
     */
    public function getTimeEntities(): array
    {
        return array_merge(
            $this->happenings,
            $this->unavailabilities,
            $this->masses,
            $this->meetings
        );
    }

    /**
     * @return bool
     */
    public function isSheetAttendingTheEvent(): bool
    {
        return null === $this->cancelAttendanceUnavailabilityView;
    }

    /**
     * @param TimeRangeInterface $slot
     *
     * @return MeetingView[]
     */
    public function getMeetingViewBySlot(TimeRangeInterface $slot): array
    {
        $index = $slot->getBegin()->format(self::SLOT_DATE_HOUR_INDEX);

        return $this->meetingViewsByDateBegin[$index] ?? [];
    }

    /**
     * @param TimeRangeInterface $slot
     *
     * @return MassUnavailabilityView[]
     */
    public function getMassUnavailabilityViewsBySlot(TimeRangeInterface $slot): array
    {
        $index = $slot->getBegin()->format(self::SLOT_DATE_HOUR_INDEX);

        return $this->massUnavailabilityViewsByDateBegin[$index] ?? [];
    }

    public function hasMeetingsOrMassUnavailabilityBySlot(TimeRangeInterface $slot): bool
    {
        return !empty($this->getMeetingViewBySlot($slot)) || !empty($this->getMassUnavailabilityViewsBySlot($slot));
    }
}
