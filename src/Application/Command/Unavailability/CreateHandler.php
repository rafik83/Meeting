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

        $day = $create->day->getDay();
        $dayUTC = clone $day;

        $beginTimeHour = intval($create->time['begin']['hour']) - ($dayUTC->getOffset() / 3600);
        $beginTime = gmmktime(
            $beginTimeHour,
            $create->time['begin']['minute'],
            0,
            intval($day->format('n')),
            intval($day->format('j')),
            intval($day->format('Y'))
        );

        $endTimeHour = intval($create->time['end']['hour']) - ($dayUTC->getOffset() / 3600);
        $endTime = gmmktime(
            $endTimeHour,
            $create->time['end']['minute'],
            0,
            intval($day->format('n')),
            intval($day->format('j')),
            intval($day->format('Y'))
        );

        $begin = clone $day;
        $begin->setTimestamp($beginTime);

        $end = clone $day;
        $end->setTimestamp($endTime);

        // If time selected is out of range of the selected day
        if ($begin > $create->day->getEndTime()) {
            throw new TimeOutOfRangeException($create->day, TimeOutOfRangeException::END);
        } elseif ($end < $create->day->getStartTime()) {
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

        // Merger unavailability

        // Truncate unavailability with start time and end time of day if out of period

        // Save unavailability
        foreach ($create->participants as $participant) {
            $this->unavailabilityRepository->add(new Unavailability($participant, $begin, $end));
        }
    }
}
