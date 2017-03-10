<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Planning\Formatter;

use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Planning\PlanningViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\AbstractTimeEntityView;
use Proximum\Vimeet\Application\View\Planning\DayView;
use Proximum\Vimeet\Application\View\Planning\day\HappeningParticipationView;
use Proximum\Vimeet\Application\View\Planning\day\MassView;
use Proximum\Vimeet\Application\View\Planning\day\AssignmentView;
use Proximum\Vimeet\Application\View\Planning\day\MeetingView;
use Proximum\Vimeet\Application\View\Planning\day\UnavailabilityView;
use Proximum\Vimeet\Application\View\Planning\PlanningView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Service\MarkdownFormatter;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;

class ParticipantPlanningFormatter
{
    const TRANSLATION_DOMAIN       = 'messages';
    const TRANSLATE_MEETING        = 'planning.participant.meeting';
    const TRANSLATE_UNAVAILABILITY = 'planning.participant.unavailability';
    const TRANSLATE_TIME_ENTITY    = 'planning.participant.time';

    /**
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @var UnallocatedFormatter
     */
    private $unallocatedFormatter;

    /**
     * @var AgendaViewQueryHandler
     */
    private $agendaViewQueryHandler;

    /**
     * @var PlanningViewQueryHandler
     */
    private $planningViewQueryHandler;

    /**
     * @param TranslatorAdapter        $translator
     * @param UnallocatedFormatter     $unallocatedFormatter
     * @param AgendaViewQueryHandler   $agendaViewQueryHandler
     * @param PlanningViewQueryHandler $planningViewQueryHandler
     */
    public function __construct(
        TranslatorAdapter $translator,
        UnallocatedFormatter $unallocatedFormatter,
        AgendaViewQueryHandler $agendaViewQueryHandler,
        PlanningViewQueryHandler $planningViewQueryHandler
    ) {
        $this->translator             = $translator;
        $this->unallocatedFormatter   = $unallocatedFormatter;
        $this->agendaViewQueryHandler = $agendaViewQueryHandler;
        $this->planningViewQueryHandler = $planningViewQueryHandler;
    }

    /**
     * Format the planning of the participant
     *
     * @param Participant $participant
     * @param string      $userLocale
     *
     * @return string
     */
    public function formatPlanningFromParticipant(Participant $participant, $userLocale)
    {
        $sheet  = $participant->getSheet();
        $event  = $sheet->getEvent();
        $planning = $this->getAgendaFromParticipant($event, $participant, $userLocale);

        return $this->formatPlanningFromAgenda($planning, $userLocale);
    }

    /**
     * @param Participant $participant
     * @param string      $userLocale
     *
     * @return string
     */
    public function formatPlanningFromParticipantWithUnallocated(Participant $participant, $userLocale)
    {
        $sheet  = $participant->getSheet();
        $event  = $sheet->getEvent();
        $agenda = $this->getAgendaFromParticipant($event, $participant, $userLocale);

        return $this->formatPlanningFromAgendaWithUnallocated($sheet, $agenda, $userLocale);
    }

    /**
     * @param Event $event
     */
    public function preloadPlanningHandler(Event $event)
    {
        $this->planningViewQueryHandler->preload($event);
    }

    /**
     * @param Event       $event
     * @param Participant $participant
     * @param string      $userLocale
     *
     * @return PlanningView
     */
    private function getAgendaFromParticipant(Event $event, Participant $participant, $userLocale)
    {
        return $this->planningViewQueryHandler->handle(new PlanningViewQuery($event, $participant, $userLocale));
    }

    /**
     * Format the planning of the participant from the PlanningView
     *
     * @param PlanningView $planning
     * @param string       $userLocale
     *
     * @return string
     */
    public function formatPlanningFromAgenda(PlanningView $planning, $userLocale)
    {
        return $this->format($planning->days, $userLocale);
    }

    /**
     * Format the planning of the participant with the sheet unallocated
     *
     * @param Sheet      $sheet
     * @param PlanningView $planningView
     * @param string     $userLocale
     *
     * @return string
     */
    public function formatPlanningFromAgendaWithUnallocated(Sheet $sheet, PlanningView $planningView, $userLocale)
    {
        $planning    = $this->format($planningView->days, $userLocale);
        $unallocated = $this->unallocatedFormatter->format($sheet, $userLocale);

        if (empty($unallocated)) {
            return $planning;
        }

        return MarkdownFormatter::newLine($planning) . $unallocated;
    }

    /**
     * @param DayView[] $days
     * @param string    $participantLocale
     *
     * @return string
     */
    private function format(array $days, $participantLocale)
    {
        $formatted    = '';
        $dayFormatter = new \IntlDateFormatter($participantLocale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);

        foreach ($days as $day) {
            // Sort the time entities of the day by time asc
            $timeEntities = $this->sortChronologicalOrder($day->getTimeEntities());

            // Display the day name
            $formatted .= MarkdownFormatter::newLine(MarkdownFormatter::bold(
                ucfirst($this->getFormattedDate($dayFormatter, $day->getDay())))
            );

            // Display the happening, mass, unavailability, meeting
            $formatted .= MarkdownFormatter::newLine(MarkdownFormatter::newLine(
                $this->formatTimeEntities($timeEntities, $participantLocale)))
            ;
        }

        return $formatted;
    }

    /**
     * @param AbstractTimeEntityView[] $timeEntities
     * @param string                   $participantLocale
     *
     * @return string
     */
    private function formatTimeEntities(array $timeEntities, $participantLocale)
    {
        $formattedTimes = [];
        $timeFormatter  = new \IntlDateFormatter($participantLocale, \IntlDateFormatter::NONE, \IntlDateFormatter::SHORT);

        foreach ($timeEntities as $timeEntity) {
            // Display the time of begin and end of the time entity
            $formatted = $this->translator->trans(
                self::TRANSLATE_TIME_ENTITY,
                [
                    '%beginHour%' => $this->getFormattedDate($timeFormatter, $timeEntity->begin),
                    '%endHour%' => $this->getFormattedDate($timeFormatter, $timeEntity->end),
                ],
                self::TRANSLATION_DOMAIN,
                $participantLocale
            );

            // Display the information of the time entity (meeting, mass, unavailability, happening)
            if ($timeEntity instanceof MeetingView) {
                $formatted .= $this->translator->trans(
                    self::TRANSLATE_MEETING,
                    ['%sheetMet%' => $timeEntity->sheetMetTitle, '%spotRef%'  => $timeEntity->spotRef,],
                    self::TRANSLATION_DOMAIN,
                    $participantLocale
                );
            } elseif ($timeEntity instanceof MassView || $timeEntity instanceof HappeningParticipationView || $timeEntity instanceof AssignmentView) {
                $formatted .= $timeEntity->title;
            } elseif ($timeEntity instanceof UnavailabilityView) {
                if ($timeEntity->hasMessage()) {
                    $formatted .= $timeEntity->message;
                } else {
                    $formatted .= $this->translator
                        ->trans(self::TRANSLATE_UNAVAILABILITY, [], self::TRANSLATION_DOMAIN, $participantLocale)
                    ;
                }
            }

            $formattedTimes[] = $formatted;
        }

        return MarkdownFormatter::lists($formattedTimes);
    }

    /**
     * @param array $timeEntities
     *
     * @return array
     */
    private function sortChronologicalOrder(array $timeEntities)
    {
        usort($timeEntities, function(AbstractTimeEntityView $first, AbstractTimeEntityView $second) {
            return $first->begin > $second->begin;
        });

        return $timeEntities;
    }

    /**
     * @param \IntlDateFormatter  $formatter
     * @param \DateTimeInterface $date
     *
     * @return string
     */
    private function getFormattedDate(\IntlDateFormatter $formatter, \DateTimeInterface $date)
    {
        $formatted = $formatter->format($date);

        return !is_bool($formatted) ? $formatted : '';
    }
}
