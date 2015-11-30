<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Schedule;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ScheduleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\View\ScheduleSlotView;

class ScheduleBuilder
{
    /**
     * @var ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ScheduleBuilder constructor.
     *
     * @param ScheduleRepositoryInterface               $scheduleRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param HappeningRepositoryInterface              $happeningRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param ParticipantInfoGuesser                    $participantInfoGuesser
     */
    public function __construct(
        ScheduleRepositoryInterface $scheduleRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        HappeningRepositoryInterface $happeningRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->scheduleRepository               = $scheduleRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->happeningRepository              = $happeningRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->participantInfoGuesser           = $participantInfoGuesser;
    }

    /**
     * @param Participant $participant
     *
     * @return array
     */
    public function buildForParticipant(Participant $participant)
    {
        $schedules = $this->scheduleRepository->findByEvent($participant->getSheet()->getEvent());

        $participantSchedule = [
            'participant' => $participant,
            'name'        => $this->participantInfoGuesser->guessParticipantInfo($participant),
            'schedules'   => [],
        ];

        foreach ($schedules as $i => $schedule) {
            $participantSchedule['schedules'][$i] = [
                'id'      => $schedule->getId(),
                'date'    => $schedule->getDate(),
                'columns' => [
                    'meeting'        => $this->buildMeetings($schedule),
                    'happening'      => $this->buildHappenings($schedule, $participant),
                    'unavailability' => $this->buildUnavailabilities($schedule, $participant),
                ],
            ];
        }

        return $participantSchedule;
    }

    /**
     * @param Schedule    $schedule
     *
     * @return array
     */
    private function buildMeetings(Schedule $schedule)
    {
        $slots = [];

        foreach ($schedule->getMeetingSlots() as $meetingSlot) {
            $slots[] = new ScheduleSlotView(
                $meetingSlot->getId(),
                'Vide',
                $meetingSlot->getBegin(),
                $meetingSlot->getEnd(),
                false
            );
        }

        $this->sort($slots);

        return $slots;
    }

    /**
     * @param Schedule    $schedule
     * @param Participant $participant
     *
     * @return array
     */
    private function buildHappenings(Schedule $schedule, Participant $participant)
    {
        $slots = [];

        $participations = $this->happeningRepository->findByScheduleAndParticipant($schedule, $participant);

        foreach ($schedule->getHappenings() as $happening) {
            $slots[] = new ScheduleSlotView(
                $happening->getId(),
                $happening->getTitle(),
                $happening->getBegin(),
                $happening->getEnd(),
                in_array($happening, $participations)
            );
        }

        $this->sort($slots);

        return $slots;
    }

    /**
     * @param Schedule    $schedule
     * @param Participant $participant
     *
     * @return array
     */
    private function buildUnavailabilities(Schedule $schedule, Participant $participant)
    {
        $slots = [];

        $unavailabilities = $this->unavailabilityRepository->findByScheduleAndParticipant($schedule, $participant);

        foreach ($unavailabilities as $unavailability) {
            $slots[] = new ScheduleSlotView(
                $unavailability->getId(),
                'Indisponible',
                $unavailability->getBegin(),
                $unavailability->getEnd(),
                true
            );
        }

        $blockingHappeningParticipations = $this->happeningParticipationRepository->findBlockingByScheduleAndParticipant($schedule, $participant);

        foreach ($blockingHappeningParticipations as $blockingHappeningParticipation) {
            $slots[] = new ScheduleSlotView(
                $blockingHappeningParticipation->getId(),
                $blockingHappeningParticipation->getHappening()->getTitle(),
                $blockingHappeningParticipation->getHappening()->getBegin(),
                $blockingHappeningParticipation->getHappening()->getEnd(),
                false
            );
        }

        $this->sort($slots);

        return $slots;
    }

    /**
     * @param array $slots
     */
    private function sort(array &$slots)
    {
        usort($slots, function (ScheduleSlotView $one, ScheduleSlotView $another) {
            return $one->begin->getTimestamp() - $another->begin->getTimestamp();
        });
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    public function buildForSheet(Sheet $sheet)
    {
        $participantSchedules = [];

        foreach ($sheet->getParticipants() as $participant) {
            $participantSchedules[] = $this->buildForParticipant($participant);
        }

        return $participantSchedules;
    }
}
