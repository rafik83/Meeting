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
use Proximum\Vimeet\Domain\Model\Sheet;
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
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ScheduleBuilder constructor.
     *
     * @param ScheduleRepositoryInterface               $scheduleRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param HappeningRepositoryInterface $happeningRepository
     * @param ParticipantInfoGuesser                    $participantInfoGuesser
     */
    public function __construct(
        ScheduleRepositoryInterface $scheduleRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        HappeningRepositoryInterface $happeningRepository,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->scheduleRepository               = $scheduleRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->happeningRepository = $happeningRepository;
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
                    'meetingSlots'   => [],
                    'happenings'      => [],
                    'unavailabilities' => [],
                ],
            ];

            // Meeting slots
            $meetingSlots = $schedule->getMeetingSlots();
            foreach ($meetingSlots as $meetingSlot) {
                $participantSchedule['schedules'][$i]['columns']['meeting'][] = new ScheduleSlotView(
                    $meetingSlot->getId(),
                    'Créneau de RdV',
                    $meetingSlot->getBegin(),
                    $meetingSlot->getEnd(),
                    false
                );
            }

            // Happening
            $happenings = $schedule->getHappenings();
            $participations = $this->happeningRepository->findByScheduleAndParticipant($schedule, $participant);
            foreach ($happenings as $happening) {
                $participantSchedule['schedules'][$i]['columns']['happening'][] = new ScheduleSlotView(
                    $happening->getId(),
                    $happening->getTitle(),
                    $happening->getBegin(),
                    $happening->getEnd(),
                    in_array($happening, $participations)
                );
            }

            // Unavailabilities
            $unavailabilities = $this->unavailabilityRepository->findByScheduleAndParticipant($schedule, $participant);
            foreach ($unavailabilities as $unavailability) {
                $participantSchedule['schedules'][$i]['columns']['unavailability'][] = new ScheduleSlotView(
                    $unavailability->getId(),
                    'Indisponible',
                    $unavailability->getBegin(),
                    $unavailability->getEnd(),
                    false
                );
            }

            // Sort
            $this->sort($participantSchedule['schedules'][$i]['columns']['meetingSlots']);
            $this->sort($participantSchedule['schedules'][$i]['columns']['happenings']);
            $this->sort($participantSchedule['schedules'][$i]['columns']['unavailabilities']);
        }

        return $participantSchedule;
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
