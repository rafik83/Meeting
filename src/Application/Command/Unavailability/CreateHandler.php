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
        $dayCloned = clone $create->day->getDay();
        $dayCloned->setTimeZone(new \DateTimeZone($create->event->getTimeZone()));

        $begin = clone $dayCloned;
        $begin->modify(sprintf('%s:%s', $create->time['begin']['hour'], $create->time['begin']['minute']));
        $begin->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

        $end = clone $dayCloned;
        $end->modify(sprintf('%s:%s', $create->time['end']['hour'], $create->time['end']['minute']));
        $end->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

        return [$begin, $end];
    }
}
