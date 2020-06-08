<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\AddUnavailabilityEvent;
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotCreateUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotMergeUnavailabilityWithANotCreatedByUserUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsWithUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class CreateHandler
{
    /** @var UnavailabilityRepositoryInterface */
    private $unavailabilityRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    public function __construct(
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->participantRepository    = $participantRepository;
        $this->participantInfoGuesser   = $participantInfoGuesser;
        $this->eventDispatcher          = $eventDispatcher;
    }

    /**
     * @param Create $create
     *
     * @throws CanNotCreateUnavailabilityException
     * @throws NoParticipantSelectedException
     * @throws ParticipantsSelectedWithMeetingOrHappeningException
     * @throws TimeOutOfRangeException
     * @throws ParticipantsWithUnavailabilityException
     */
    public function handle(Create $create): void
    {
        $locale = $create->locale;

        if (!$create->sheet->attend()) {
            throw new CanNotCreateUnavailabilityException('The sheet does not attend the event');
        }

        if (empty($create->participants)) {
            throw new NoParticipantSelectedException();
        }
        list($begin, $end) = $this->prepareBeginAndEnd($create);

        $this->checkTimeOutOfDay($create, $begin, $end);

        $this->checkParticipantsConflict($create, $begin, $end, $locale);

        $this->truncateOvertime($create, $begin, $end);

        /** @var Unavailability[] $unavailabilities */
        $unavailabilities = [];
        $participantsWithOverlapUnavailability = [];

        foreach ($create->participants as $participant) {
            try {
                $unavailabilities[] = $this->getUnavailabilityForParticipant($participant, $begin, $end, $create->message);
            } catch (CanNotMergeUnavailabilityWithANotCreatedByUserUnavailabilityException $canNotMergeUnavailabilityWithANotCreatedByUserUnavailabilityException) {
                $participantsWithOverlapUnavailability[] = $participant;
            }
        }

        if (!empty($participantsWithOverlapUnavailability)) {
            throw new ParticipantsWithUnavailabilityException(
                $this->getParticipantsNames($participantsWithOverlapUnavailability, $locale)
            );
        }

        foreach ($unavailabilities as $unavailability) {
            $this->unavailabilityRepository->add($unavailability);

            $this->eventDispatcher->dispatch(
                Events::UNAVAILABILITY_ADDED,
                new AddUnavailabilityEvent($unavailability->getUser(), $unavailability->getEvent())
            );
        }
    }

    /**
     * @param Participant        $participant
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param null|string        $message
     *
     * @throws CanNotMergeUnavailabilityWithANotCreatedByUserUnavailabilityException
     *
     * @return Unavailability
     */
    private function getUnavailabilityForParticipant(
        Participant $participant,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        ?string $message
    ): Unavailability {
        $unavailability = new Unavailability(
            $participant->getUser(),
            $participant->getSheet()->getEvent(),
            $begin,
            $end,
            $message
        );

        $this->mergeOverlapUnavailabilities($unavailability);

        return $unavailability;
    }

    /**
     * @param Unavailability $unavailability
     *
     * @throws CanNotMergeUnavailabilityWithANotCreatedByUserUnavailabilityException
     */
    private function mergeOverlapUnavailabilities(Unavailability $unavailability): void
    {
        // Here clone is required because of a bug in phophecy making test impossible
        // See https://github.com/phpspec/prophecy/issues/75
        $overlapUnavailabilities = $this->unavailabilityRepository->getOverlapUnavailabilities(clone $unavailability);

        foreach ($overlapUnavailabilities as $overlapUnavailability) {
            if (!$overlapUnavailability->isCreatedByUser()) {
                $isOverlapUnavailabilitySideBySide = $overlapUnavailability->getBegin() == $unavailability->getEnd()
                    || $overlapUnavailability->getEnd() == $unavailability->getBegin();

                if (!$isOverlapUnavailabilitySideBySide) {
                    throw new CanNotMergeUnavailabilityWithANotCreatedByUserUnavailabilityException();
                }

                continue;
            }

            $unavailability->merge($overlapUnavailability);
            $this->unavailabilityRepository->remove($overlapUnavailability);
        }
    }

    private function truncateOvertime(Create $create, \DateTimeInterface &$begin, \DateTimeInterface &$end): void
    {
        // Truncate unavailability with start time and end time of day if out of period
        if ($begin < $create->day->getBegin()) {
            $begin = clone $create->day->getBegin();
            $begin->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        }

        if ($end > $create->day->getEnd()) {
            $end = clone $create->day->getEnd();
            $end->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
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
    ): void {
        // If Participant selected have conflict with happening or meeting
        $participantsWithoutConflict = $this
            ->participantRepository
            ->getParticipantsWithoutMeetingAndHappening($create->participants, $begin, $end);

        $participantWithConflict = array_filter(
            $create->participants,
            static function (Participant $participant) use ($participantsWithoutConflict) {
                return !\in_array($participant, $participantsWithoutConflict, true);
            }
        );

        if (count($participantWithConflict) > 0) {
            throw new ParticipantsSelectedWithMeetingOrHappeningException(
                $this->getParticipantsNames($participantWithConflict, $locale)
            );
        }
    }

    /**
     * @param Participant[] $participants
     * @param string        $locale
     *
     * @return string[]
     */
    private function getParticipantsNames(array $participants, string $locale): array
    {
        return array_map(
            function (Participant $participant) use ($locale) {
                return $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale);
            },
            $participants
        );
    }

    /**
     * @param Create             $create
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     *
     * @throws TimeOutOfRangeException
     */
    private function checkTimeOutOfDay(Create $create, \DateTimeInterface $begin, \DateTimeInterface $end): void
    {
        // If time selected is out of range of the selected day
        if ($begin >= $create->day->getEnd()) {
            throw new TimeOutOfRangeException($create->day, TimeOutOfRangeException::END);
        } elseif ($end <= $create->day->getBegin()) {
            throw new TimeOutOfRangeException($create->day, TimeOutOfRangeException::BEGIN);
        }
    }

    /**
     * @param Create $create
     *
     * @return array
     */
    private function prepareBeginAndEnd(Create $create): array
    {
        $dayCloned = clone $create->day->getBegin();

        $begin = clone $dayCloned;
        $begin->modify(sprintf('%s:%s', $create->time['begin']['hour'], $create->time['begin']['minute']));
        $begin->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

        $end = clone $dayCloned;
        $end->modify(sprintf('%s:%s', $create->time['end']['hour'], $create->time['end']['minute']));
        $end->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

        return [$begin, $end];
    }
}
