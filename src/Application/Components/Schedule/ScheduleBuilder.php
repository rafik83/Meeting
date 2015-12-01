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
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MeetingRepositoryInterface;
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
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

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
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ScheduleBuilder constructor.
     *
     * @param ScheduleRepositoryInterface               $scheduleRepository
     * @param MeetingRepositoryInterface                $meetingRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param HappeningRepositoryInterface              $happeningRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param SheetInfoGuesser                          $sheetInfoGuesser
     * @param ParticipantInfoGuesser                    $participantInfoGuesser
     */
    public function __construct(
        ScheduleRepositoryInterface $scheduleRepository,
        MeetingRepositoryInterface $meetingRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        HappeningRepositoryInterface $happeningRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->scheduleRepository               = $scheduleRepository;
        $this->meetingRepository                = $meetingRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->happeningRepository              = $happeningRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->sheetInfoGuesser                 = $sheetInfoGuesser;
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
                    'meeting'        => $this->buildMeetings($schedule, $participant),
                    'happening'      => $this->buildHappenings($schedule, $participant),
                    'unavailability' => $this->buildUnavailabilities($schedule, $participant),
                ],
            ];
        }

        return $participantSchedule;
    }

    /**
     * @param Schedule    $schedule
     * @param Participant $participant
     *
     * @return array
     */
    private function buildMeetings(Schedule $schedule, Participant $participant)
    {
        $slots = [];

        foreach ($schedule->getMeetingSlots() as $meetingSlot) {
            $slots[$meetingSlot->getId()] = new ScheduleSlotView(
                $meetingSlot->getId(),
                'Vide',
                $meetingSlot->getBegin(),
                $meetingSlot->getEnd(),
                false
            );
        }

        $meetings = $this->meetingRepository->findScheduledByScheduleAndParticipant($schedule, $participant);

        foreach ($meetings as $meeting) {
            $sheet = $meeting->getFrom() === $participant->getSheet() ? $meeting->getTo() : $meeting->getFrom();

            $slots[$meeting->getMeetingSlot()->getId()] = new ScheduleSlotView(
                $meeting->getId(),
                $this->sheetInfoGuesser->guessSheetInfo($sheet),
                $meeting->getMeetingSlot()->getBegin(),
                $meeting->getMeetingSlot()->getEnd(),
                true
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
