<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class CreateHandler
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     * @param ParticipantRepositoryInterface    $participantRepository
     * @param ParticipantInfoGuesser            $participantInfoGuesser
     */
    public function __construct(
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->participantRepository    = $participantRepository;
        $this->participantInfoGuesser   = $participantInfoGuesser;
    }

    /**
     * @param Create $create
     *
     * @throws NoParticipantSelectedException
     * @throws ParticipantsSelectedWithMeetingOrHappeningException
     * @throws TimeOutOfRangeException
     */
    public function handle(Create $create)
    {
        $locale = $create->locale;

        if (empty($create->participants)) {
            throw new NoParticipantSelectedException();
        }
        list($begin, $end) = $this->prepareBeginAndEnd($create);

        $this->checkTimeOutOfDay($create, $begin, $end);

        $this->checkParticipantsConflict($create, $begin, $end, $locale);

        $this->truncateOvertime($create, $begin, $end);

        // Merge overlap unavailability and Save unavailability
        foreach ($create->participants as $participant) {
            $unavailability = new Unavailability($participant, $begin, $end);
            $this->mergeOverlapUnavailabilities($unavailability);
            $this->unavailabilityRepository->add($unavailability);
        }
    }

    /**
     * @param Unavailability $unavailability
     */
    private function mergeOverlapUnavailabilities(Unavailability $unavailability)
    {
        // Here clone is required because of a bug in phophecy making test impossible
        // See https://github.com/phpspec/prophecy/issues/75
        $overlapUnavailabilities = $this->unavailabilityRepository->getOverlapUnavailabilities(clone $unavailability);

        foreach ($overlapUnavailabilities as $overlapUnavailability) {
            $unavailability->merge($overlapUnavailability);
            $this->unavailabilityRepository->remove($overlapUnavailability);
        }
    }

    /**
     * @param Create             $create
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    private function truncateOvertime(Create $create, \DateTimeInterface &$begin, \DateTimeInterface &$end)
    {
        // Truncate unavailability with start time and end time of day if out of period
        if ($begin < $create->day->getStartTime()) {
            $begin = clone $create->day->getStartTime();
        }

        if ($end > $create->day->getEndTime()) {
            $end = clone $create->day->getEndTime();
        }
    }

    /**
     * @param string|int $year
     * @param string|int $month
     * @param string|int $day
     * @param string|int $hour
     * @param string|int $minute
     *
     * @return string
     */
    private function getStringifyDate($year, $month, $day, $hour, $minute)
    {
        return sprintf(
            '%s-%s-%s %s:%s:00.000',
            $year,
            $month,
            $day,
            $hour,
            $minute
        );
    }

    /**
     * @param string|int $year
     * @param string|int $month
     * @param string|int $day
     * @param string|int $hour
     * @param string|int $minute
     * @param string     $timeZone
     *
     * @return \DateTimeInterface
     */
    private function getDateTimeForDate($year, $month, $day, $hour, $minute, $timeZone = null)
    {
        $stringDate = $this->getStringifyDate($year, $month, $day, $hour, $minute);

        return $timeZone !== null
            ? new \DateTime($stringDate, new \DateTimeZone($timeZone))
            : new \DateTime($stringDate)
        ;
    }

    /**
     * @param Create             $create
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $locale
     *
     * @throws ParticipantsSelectedWithMeetingOrHappeningException
     */
    private function checkParticipantsConflict(
        Create $create,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $locale
    ) {
        // If Participant selected have conflict with happening or meeting
        $participantsWithoutConflict = $this
            ->participantRepository
            ->getParticipantsWithoutMeetingAndHappening($create->participants, $begin, $end);

        $participantWithConflict = array_filter(
            $create->participants,
            function (Participant $participant) use ($participantsWithoutConflict) {
                return !in_array($participant, $participantsWithoutConflict);
            }
        );

        if (count($participantWithConflict) > 0) {
            throw new ParticipantsSelectedWithMeetingOrHappeningException(
                array_map(function (Participant $participant) use ($locale) {
                    return $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale);
                }, $participantWithConflict)
            );
        }
    }

    /**
     * @param Create             $create
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     *
     * @throws TimeOutOfRangeException
     */
    private function checkTimeOutOfDay(Create $create, \DateTimeInterface $begin, \DateTimeInterface  $end)
    {
        // If time selected is out of range of the selected day
        if ($begin >= $create->day->getEndTime()) {
            throw new TimeOutOfRangeException($create->day, TimeOutOfRangeException::END);
        } elseif ($end <= $create->day->getStartTime()) {
            throw new TimeOutOfRangeException($create->day, TimeOutOfRangeException::BEGIN);
        }
    }

    /**
     * @param Create $create
     *
     * @return array
     */
    private function prepareBeginAndEnd(Create $create)
    {
        // Clone the selected day
        // Recreate a day with the timezone of the event
        // To be able to get the offset between the two dates
        // Then remove this offset to the hour selected by the user
        // To be able to recreate UTC dates
        $day          = $create->day->getDay();
        $endTime      = $create->day->getStartTime();
        $dayTimeZonedBegin = $this->getDateTimeForDate(
            $day->format('Y'),
            $day->format('n'),
            $day->format('j'),
            $day->format('H'),
            $day->format('i'),
            $create->event->getTimeZone()
        );
        $dayTimeZonedEnd = $this->getDateTimeForDate(
            $endTime->format('Y'),
            $endTime->format('n'),
            $endTime->format('j'),
            $endTime->format('H'),
            $endTime->format('i'),
            $create->event->getTimeZone()
        );

        $offsetInHour = $dayTimeZonedBegin->getOffset() / 3600;

        // BEGIN
        // THIS IS IMPORTANT TO GET THE CORRECT DAY ON THE TIMEZONED DAY
        if (intval($endTime->format('H')) + $offsetInHour < 0) {
            $dayTimeZonedBegin->modify('-1 day');
        }
        if (intval($endTime->format('H')) + $offsetInHour > 24) {
            $dayTimeZonedBegin->modify('+1 day');
        }
        //

        // END
        // THIS IS IMPORTANT TO GET THE CORRECT DAY ON THE TIMEZONED DAY
        if (intval($endTime->format('H')) + $offsetInHour < 0) {
            $dayTimeZonedEnd->modify('-1 day');
        }
        if (intval($endTime->format('H')) + $offsetInHour > 24) {
            $dayTimeZonedEnd->modify('+1 day');
        }
        //

        $beginTimeHour = intval($create->time['begin']['hour']) - $offsetInHour;
        $beginTimeDay  = intval($dayTimeZonedBegin->format('j'));

        // In case of a negative hour, cause by a starting early event
        // Reduce the day by one
        // And change the negative hour to it correct position on the previous day
        if ($beginTimeHour < 0) {
            $beginTimeDay--;
            $beginTimeHour = 24 + $beginTimeHour;
        }

        // In case of a > 24 hour, cause by a starting late event
        // Increase the day by one
        // And change the > 24 hour to it correct position on the next day
        if ($beginTimeHour > 24) {
            $beginTimeHour = ($beginTimeHour - 24);
            $beginTimeDay += 1;
        }

        $begin = $this->getDateTimeForDate(
            $day->format('Y'),
            $day->format('n'),
            $beginTimeDay,
            $beginTimeHour,
            $create->time['begin']['minute']
        );

        $endTimeHour = intval($create->time['end']['hour']) - $offsetInHour;
        $endTimeDay  = intval($dayTimeZonedEnd->format('j'));

        // In case of a negative hour, cause by a starting early event
        // Reduce the day by one
        // And change the negative hour to it correct position on the previous day
        if ($endTimeHour < 0) {
            $endTimeDay--;
            $endTimeHour = 24 + $endTimeHour;
        }
        if ($endTimeHour >= 24) {
            $endTimeHour = ($endTimeHour - 24);
            $endTimeDay += 1;
        }

        $end = $this->getDateTimeForDate(
            $day->format('Y'),
            $day->format('n'),
            $endTimeDay,
            $endTimeHour,
            $create->time['end']['minute']
        );

        return [$begin, $end];
    }
}
