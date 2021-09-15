<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateEventDatesToCurrentDateHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var MassAssignmentRepositoryInterface */
    private $massAssignmentRepository;

    /** @var UnavailabilityRepositoryInterface */
    private $unavailabilityRepository;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var MassRepositoryInterface */
    private $massRepository;

    /** @var bool */
    private $featureEnabled;

    /** @var DayRepositoryInterface */
    private $dayRepository;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param MeetingSlotRepositoryInterface    $meetingSlotRepository
     * @param MassAssignmentRepositoryInterface $massAssignmentRepository
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     * @param HappeningRepositoryInterface      $happeningRepository
     * @param MassRepositoryInterface           $massRepository
     * @param bool                              $featureEnabled
     * @param DayRepositoryInterface            $dayRepository
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        HappeningRepositoryInterface $happeningRepository,
        MassRepositoryInterface $massRepository,
        bool $featureEnabled,
        DayRepositoryInterface $dayRepository
    ) {
        $this->eventRepository          = $eventRepository;
        $this->meetingSlotRepository    = $meetingSlotRepository;
        $this->massAssignmentRepository = $massAssignmentRepository;
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->happeningRepository      = $happeningRepository;
        $this->massRepository           = $massRepository;
        $this->featureEnabled           = $featureEnabled;
        $this->dayRepository            = $dayRepository;
    }

    /**
     * @param UpdateEventDatesToCurrentDate $command
     */
    public function handle(UpdateEventDatesToCurrentDate $command)
    {
        if (!$this->featureEnabled) {
            throw new AccessDeniedHttpException('This feature is disabled');
        }

        $timeZone = $command->event->getTimeZone();

        $slots            = $this->meetingSlotRepository->findByEvent($command->event);
        $masses           = $this->massRepository->findByEvent($command->event);
        $massAssignments  = $this->massAssignmentRepository->findByEvent($command->event);
        $unavailabilities = $this->unavailabilityRepository->getByEvent($command->event);
        $happenings       = $this->happeningRepository->findByEvent($command->event);
        $days             = $this->dayRepository->findByEvent($command->event);

        foreach ($this->formatPerDay($happenings) as $dayNumber => $happenings) {
            foreach ($happenings as $happening) {
                $happening->setBegin($this->update($timeZone, $command->beginDate, $happening->getBegin(), $dayNumber));
                $happening->setEnd($this->update($timeZone, $command->beginDate, $happening->getEnd(), $dayNumber));
                $this->happeningRepository->set($happening);
            }
        }

        foreach ($this->formatPerDay($unavailabilities) as $dayNumber => $unavailabilities) {
            foreach ($unavailabilities as $unavailability) {
                $unavailability->update(
                    $this->update($timeZone, $command->beginDate, $unavailability->getBegin(), $dayNumber),
                    $this->update($timeZone, $command->beginDate, $unavailability->getEnd(), $dayNumber)
                );
                $this->unavailabilityRepository->set($unavailability);
            }
        }

        foreach ($this->formatPerDay($massAssignments) as $dayNumber => $massAssignments) {
            foreach ($massAssignments as $massAssignment) {
                $massAssignment->update(
                    $this->update($timeZone, $command->beginDate, $massAssignment->getBegin(), $dayNumber),
                    $this->update($timeZone, $command->beginDate, $massAssignment->getEnd(), $dayNumber),
                    $massAssignment->isEnabled()
                );
                $this->massAssignmentRepository->set($massAssignment);
            }
        }

        foreach ($this->formatPerDay($slots) as $dayNumber => $slots) {
            foreach ($slots as $slot) {
                $slot->setBegin($this->update($timeZone, $command->beginDate, $slot->getBegin(), $dayNumber));
                $slot->setEnd($this->update($timeZone, $command->beginDate, $slot->getEnd(), $dayNumber));
                $this->meetingSlotRepository->set($slot);
            }
        }

        foreach ($days as $dayNumber => $day) {
            $day->setStartTime($this->update($timeZone, $command->beginDate, $day->getStartTime(), $dayNumber));
            $day->setEndTime($this->update($timeZone, $command->beginDate, $day->getEndTime(), $dayNumber));
            $this->dayRepository->set($day);
        }

        foreach ($this->formatPerDay($masses) as $dayNumber => $massesPerDay) {
            /** @var Mass $mass */
            foreach ($massesPerDay as $mass) {
                $mass->setDates(
                    $this->update($timeZone, $command->beginDate, $mass->getBegin(), $dayNumber),
                    $this->update($timeZone, $command->beginDate, $mass->getEnd(), $dayNumber)
                );
                $this->massRepository->update($mass);
            }
        }

        $command->event->getConfiguration()->setDates(
            $command->beginDate,
            $command->beginDate,
            $command->beginDate,
            null,
            null,
            null,
            $command->beginDate
        );

        $this->eventRepository->set($command->event);
    }

    private function update(
        string $timeZone,
        \DateTime $beginDate,
        \DateTimeInterface $dayDateTime,
        int $dayNumber = 0
    ): \DateTimeInterface {
        $dayDateTime = (new \DateTime())
            ->setTimestamp($dayDateTime->getTimestamp())
            ->setTimezone(new \DateTimeZone($timeZone))
        ;

        $newDate = clone $beginDate;
        $newDate->setTimezone(new \DateTimeZone($timeZone));

        if ($dayNumber > 0) {
            $newDate = $newDate->modify('+' . $dayNumber . ' day');
        }

        return $newDate
            ->setTime(
                $dayDateTime->format('H'),
                $dayDateTime->format('i'),
                $dayDateTime->format('s')
            )
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
        ;
    }

    /**
     * @param array $elements
     *
     * @return array
     */
    private function formatPerDay(array $elements): array
    {
        $dayDates = [];
        $formattedDayDates = [];

        foreach ($elements as $element) {
            $dayDates[$element->getBegin()->format('d')][] = $element;
        }

        $dayNumber = 0;
        foreach ($dayDates as $day => $dates) {
            $formattedDayDates[$dayNumber] = $dayDates[$day];
            ++$dayNumber;
        }

        return $formattedDayDates;
    }
}
