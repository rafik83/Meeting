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

        // Clone the selected day
        // Recreate a day with the timezone of the event
        // To be able to get the offset between the two dates
        // Then remove this offset to the hour selected by the user
        // To be able to recreate UTC dates
        $day          = $create->day->getDay();
        $dayTimeZoned = new \DateTime(
            $this->getStringifyDate(
                $day->format('Y'),
                $day->format('n'),
                $day->format('j'),
                $day->format('h'),
                $day->format('i')
            ),
            new \DateTimezone($create->event->getTimeZone())
        );

        $beginTimeHour = intval($create->time['begin']['hour']) - ($dayTimeZoned->getOffset() / 3600);
        $beginTimeDay  = intval($day->format('j'));

        // In case of a negative hour, cause by a starting early event
        // Reduce the day by one
        // And change the negative hour to it correct position on the previous day
        if ($beginTimeHour < 0) {
            $beginTimeDay -= 1;
            $beginTimeHour = 24 + $beginTimeHour;
        }

        $begin = new \DateTime(
            $this->getStringifyDate(
                $day->format('Y'),
                $day->format('n'),
                $beginTimeDay,
                $beginTimeHour,
                $create->time['begin']['minute']
            )
        );

        $endTimeHour = intval($create->time['end']['hour']) - ($dayTimeZoned->getOffset() / 3600);
        $endTimeDay  = $day->format('j');

        // In case of a negative hour, cause by a starting early event
        // Reduce the day by one
        // And change the negative hour to it correct position on the previous day
        if ($endTimeHour < 0) {
            $endTimeDay -= 1;
            $endTimeHour = 24 + $endTimeHour;
        }


        $end = new \DateTime(
            $this->getStringifyDate(
                $day->format('Y'),
                $day->format('n'),
                $endTimeDay,
                $endTimeHour,
                $create->time['end']['minute']
            )
        );

        // If time selected is out of range of the selected day
        if ($begin >= $create->day->getEndTime()) {
            throw new TimeOutOfRangeException($create->day, TimeOutOfRangeException::END);
        } elseif ($end <= $create->day->getStartTime()) {
            throw new TimeOutOfRangeException($create->day, TimeOutOfRangeException::BEGIN);
        }

        // If Participant selected have conflict with happening or meeting
        $participantsWithoutConflict = $this->participantRepository->getParticipantsWithoutMeetingAndHappening($create->participants, $begin, $end);

        $participantWithConflict = array_filter($create->participants, function (Participant $participant) use ($participantsWithoutConflict) {
            return !in_array($participant, $participantsWithoutConflict);
        });

        if (count($participantWithConflict) > 0) {
            throw new ParticipantsSelectedWithMeetingOrHappeningException(
                    array_map(function (Participant $participant) use ($locale) {
                        return $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale);
                    },
                    $participantWithConflict
                )
            );
        }

        // Truncate unavailability with start time and end time of day if out of period
        if ($begin < $create->day->getStartTime()) {
            $begin = clone $create->day->getStartTime();
        }

        if ($end > $create->day->getEndTime()) {
            $end = clone $create->day->getEndTime();
        }

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
            '%s-%s-%s %s:%s:00',
            $year,
            $month,
            $day,
            $hour,
            $minute
        );
    }
}
