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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\View\ScheduleSlotView;
use Proximum\Vimeet\Domain\View\ScheduleView;

class ScheduleBuilder
{
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
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * ScheduleBuilder constructor.
     *
     * @param MeetingRepositoryInterface                $meetingRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param HappeningRepositoryInterface              $happeningRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param SheetInfoGuesser                          $sheetInfoGuesser
     * @param ParticipantInfoGuesser                    $participantInfoGuesser
     * @param MeetingSlotRepositoryInterface            $meetingSlotRepository
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        HappeningRepositoryInterface $happeningRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->meetingRepository                = $meetingRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->happeningRepository              = $happeningRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->sheetInfoGuesser                 = $sheetInfoGuesser;
        $this->participantInfoGuesser           = $participantInfoGuesser;
        $this->meetingSlotRepository            = $meetingSlotRepository;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return array
     */
    public function buildForParticipant(Participant $participant, $locale)
    {
        return $this->builScheduleViewForMeetingHappeningsAndUnavailabilities($participant, $locale);
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return ScheduleView[]
     */
    private function builScheduleViewForMeetingHappeningsAndUnavailabilities(Participant $participant, $locale)
    {
        $scheduleViews = [];
        $scheduleViews = $this->buildMeetingsForScheduleViews($scheduleViews, $participant);
        $scheduleViews = $this->buildHappeningsForScheduleViews($scheduleViews, $participant, $locale);
        $scheduleViews = $this->buildUnavailabilitiesForScheduleViews($scheduleViews, $participant);

        return [
            'participant' => $participant,
            'name'        => $this->participantInfoGuesser->guessParticipantInfo($participant),
            'schedules'   => $scheduleViews,
        ];
    }

    /**
     * @param array       $scheduleViews
     * @param Participant $participant
     *
     * @return ScheduleView[]
     */
    private function buildMeetingsForScheduleViews(array $scheduleViews, Participant $participant)
    {
        $meetingSlots = $this->meetingSlotRepository->findByEvent($participant->getSheet()->getEvent());

        foreach ($meetingSlots as $meetingSlot) {
            $beginDate = clone $meetingSlot->getBegin();
            $beginDate->setTimeZone(new \DateTimeZone($participant->getSheet()->getEvent()->getTimeZone()));

            if (!empty($scheduleViews) && isset($scheduleViews[$beginDate->format('Y-m-d')])) {
                $scheduleViews[$beginDate->format('Y-m-d')]->addMeeting(
                    $meetingSlot->getId(),
                    new ScheduleSlotView(
                        $meetingSlot->getId(),
                        'Vide',
                        $meetingSlot->getBegin(),
                        $meetingSlot->getEnd(),
                        false
                    )
                );
            } else {
                $scheduleViews[$beginDate->format('Y-m-d')] = new ScheduleView(
                    $beginDate->format('Y-m-d'),
                    new \DateTime(
                        $beginDate->format('Y-m-d 00:00:00'),
                        new \DateTimeZone($participant->getSheet()->getEvent()->getTimeZone())
                    )
                );

                $scheduleViews[$beginDate->format('Y-m-d')]->addMeeting(
                    $meetingSlot->getId(),
                    new ScheduleSlotView(
                        $meetingSlot->getId(),
                        'Vide',
                        $meetingSlot->getBegin(),
                        $meetingSlot->getEnd(),
                        false
                    )
                );
            }
        }

        $meetings = $this->meetingRepository->findByParticipant($participant);

        foreach ($meetings as $meeting) {
            $sheet = $meeting->getFromSheet() === $participant->getSheet() ? $meeting->getToSheet() : $meeting->getFromSheet();
            $beginDate = clone $meetingSlot->getBegin();
            $beginDate->setTimeZone(new \DateTimeZone($participant->getSheet()->getEvent()->getTimeZone()));

            $scheduleViews[$beginDate->format('Y-m-d')]->addMeeting(
                $meeting->getSlot()->getId(),
                new ScheduleSlotView(
                    $meeting->getId(),
                    $this->sheetInfoGuesser->guessSheetInfo($sheet),
                    $meeting->getSlot()->getBegin(),
                    $meeting->getSlot()->getEnd(),
                    true
                )
            );
        }

        foreach ($scheduleViews as $scheduleView) {
            $this->sort($scheduleView->meetings);
        }

        return $scheduleViews;
    }

    /**
     * @param array       $scheduleViews
     * @param Participant $participant
     * @param string      $locale
     *
     * @return ScheduleView[]
     */
    private function buildHappeningsForScheduleViews(array $scheduleViews, Participant $participant, $locale)
    {
        $happenings     = $this->happeningRepository->findByEvent($participant->getSheet()->getEvent(), $locale);

        foreach ($happenings as $happening) {
            $beginDate = clone $happening->getBegin();
            $beginDate->setTimeZone(new \DateTimeZone($participant->getSheet()->getEvent()->getTimeZone()));
            $present = $this->happeningParticipationRepository->findByHappeningAndParticipant($happening, $participant) !== null;

            if (!empty($scheduleViews) && isset($scheduleViews[$beginDate->format('Y-m-d')])) {
                $scheduleViews[$beginDate->format('Y-m-d')]->addHappening(
                    new ScheduleSlotView(
                        $happening->getId(),
                        $happening->getTitle($locale),
                        $happening->getBegin(),
                        $happening->getEnd(),
                        $present
                    )
                );

                if ($present) {
                    $scheduleViews[$beginDate->format('Y-m-d')]->addUnavailability(
                        new ScheduleSlotView(
                            $happening->getId(),
                            $happening->getTitle($locale),
                            $happening->getBegin(),
                            $happening->getEnd(),
                            false
                        )
                    );
                }
            } else {
                $scheduleViews[$beginDate->format('Y-m-d')] = new ScheduleView(
                    $beginDate->format('Y-m-d'),
                    new \DateTime(
                        $beginDate->format('Y-m-d 00:00:00'),
                        new \DateTimeZone($participant->getSheet()->getEvent()->getTimeZone())
                    )
                );

                $scheduleViews[$beginDate->format('Y-m-d')]->addHappening(
                    new ScheduleSlotView(
                        $happening->getId(),
                        $happening->getTitle($locale),
                        $happening->getBegin(),
                        $happening->getEnd(),
                        $present
                    )
                );

                if ($present) {
                    $scheduleViews[$beginDate->format('Y-m-d')]->addUnavailability(
                        new ScheduleSlotView(
                            $happening->getId(),
                            $happening->getTitle($locale),
                            $happening->getBegin(),
                            $happening->getEnd(),
                            false
                        )
                    );
                }
            }
        }

        foreach ($scheduleViews as $scheduleView) {
            $this->sort($scheduleView->happenings);
        }

        return $scheduleViews;
    }


    /**
     * @param array       $scheduleViews
     * @param Participant $participant
     *
     * @return ScheduleView[]
     */
    private function buildUnavailabilitiesForScheduleViews(array $scheduleViews, Participant $participant)
    {
        $unavailabilities = $this->unavailabilityRepository->findByParticipant($participant);

        foreach ($unavailabilities as $unavailability) {
            $beginDate = clone $unavailability->getBegin();
            $beginDate->setTimeZone(new \DateTimeZone($participant->getSheet()->getEvent()->getTimeZone()));

            if (!empty($scheduleViews) && isset($scheduleViews[$beginDate->format('Y-m-d')])) {
                $scheduleViews[$beginDate->format('Y-m-d')]->addUnavailability(
                    new ScheduleSlotView(
                        $unavailability->getId(),
                        'Indisponible',
                        $unavailability->getBegin(),
                        $unavailability->getEnd(),
                        true
                    )
                );
            } else {
                $scheduleViews[$beginDate->format('Y-m-d')] = new ScheduleView(
                    $beginDate->format('Y-m-d'),
                    new \DateTime(
                        $beginDate->format('Y-m-d 00:00:00'),
                        new \DateTimeZone($participant->getSheet()->getEvent()->getTimeZone())
                    )
                );

                $scheduleViews[$beginDate->format('Y-m-d')]->addUnavailability(
                    new ScheduleSlotView(
                        $unavailability->getId(),
                        'Indisponible',
                        $unavailability->getBegin(),
                        $unavailability->getEnd(),
                        true
                    )
                );
            }
        }

        foreach ($scheduleViews as $scheduleView) {
            $this->sort($scheduleView->unavailabilities);
        }

        return $scheduleViews;
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
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array
     */
    public function buildForSheet(Sheet $sheet, $locale)
    {
        $participantSchedules = [];

        foreach ($sheet->getParticipants() as $participant) {
            $participantSchedules[] = $this->buildForParticipant($participant, $locale);
        }

        return $participantSchedules;
    }
}
