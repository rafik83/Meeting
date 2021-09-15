<?php

namespace Proximum\Vimeet\Application\Components\Planning\Formatter;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Planning\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Planning\PlanningViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\AbstractTimeEntityView;
use Proximum\Vimeet\Application\View\Planning\Day\AssignmentView;
use Proximum\Vimeet\Application\View\Planning\Day\HappeningParticipationView;
use Proximum\Vimeet\Application\View\Planning\Day\MassView;
use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;
use Proximum\Vimeet\Application\View\Planning\Day\ParticipantMetView;
use Proximum\Vimeet\Application\View\Planning\Day\UnavailabilityView;
use Proximum\Vimeet\Application\View\Planning\DayView;
use Proximum\Vimeet\Application\View\Planning\PlanningView;
use Proximum\Vimeet\Domain\Helper\LinkedSheetsTitle;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Service\MarkdownFormatter;

class ParticipantPlanningFormatter
{
    public const TRANSLATION_DOMAIN = 'messages';
    public const TRANSLATE_MEETING  = 'planning.participant.meeting';
    public const TRANSLATE_MEETING_MULTIPLE_SHEET = 'planning.participant.meeting_multiple_sheet';
    public const TRANSLATE_UNAVAILABILITY = 'planning.participant.unavailability';
    public const TRANSLATE_TIME_ENTITY  = 'planning.participant.time';

    /** @var TranslatorInterface */
    private $translator;

    /** @var UnallocatedFormatter */
    private $unallocatedFormatter;

    /** @var PlanningViewQueryHandler */
    private $planningViewQueryHandler;

    /** @var LinkedSheetsTitle */
    private $linkedSheetsTitle;

    /**
     * @param TranslatorInterface      $translator
     * @param UnallocatedFormatter     $unallocatedFormatter
     * @param PlanningViewQueryHandler $planningViewQueryHandler
     * @param LinkedSheetsTitle        $linkedSheetsTitle
     */
    public function __construct(
        TranslatorInterface $translator,
        UnallocatedFormatter $unallocatedFormatter,
        PlanningViewQueryHandler $planningViewQueryHandler,
        LinkedSheetsTitle $linkedSheetsTitle
    ) {
        $this->translator               = $translator;
        $this->unallocatedFormatter     = $unallocatedFormatter;
        $this->planningViewQueryHandler = $planningViewQueryHandler;
        $this->linkedSheetsTitle = $linkedSheetsTitle;
    }

    /**
     * Format the planning of the user
     *
     * @param User   $user
     * @param Event  $event
     * @param string $userLocale
     *
     * @return string
     */
    public function formatPlanningFromUserAndEvent(User $user, Event $event, $userLocale)
    {
        $planning = $this->getPlanningFromUser($event, $user, $userLocale);

        return $this->formatPlanningFromPlanningView($planning, $userLocale);
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $userLocale
     *
     * @return string
     */
    public function formatPlanningFromUserAndEventWithUnallocated(User $user, Event $event, $userLocale)
    {
        $planningView = $this->getPlanningFromUser($event, $user, $userLocale);

        return $this->formatPlanningFromPlanningViewWithUnallocatedForUser(
            $event,
            $user,
            $planningView,
            $userLocale
        );
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $userLocale
     *
     * @return FormattedPlanningView
     */
    public function formatPlanningByDayFromUserAndEventWithUnallocated(
        User $user,
        Event $event,
        $userLocale
    ): FormattedPlanningView {
        $planningView = $this->getPlanningFromUser($event, $user, $userLocale);

        $plannings = [];

        foreach ($planningView->days as $key => $day) {
            $plannings[$key] = $this->format(
                [$day],
                $userLocale,
                $planningView->eventTimeZone,
                $planningView->isUserMultipleSheet
            );
        }

        $unallocated = $this->unallocatedFormatter->formatForUser(
            $event,
            $user,
            $userLocale,
            $planningView->isUserMultipleSheet
        );

        return new FormattedPlanningView($plannings, $unallocated);
    }

    /**
     * @param Event $event
     */
    public function preloadPlanningHandlerForEvent(Event $event): void
    {
        $this->planningViewQueryHandler->preloadForEvent($event);
    }

    /**
     * @param $event
     */
    public function resetPlanningHandlerForEvent($event): void
    {
        $this->planningViewQueryHandler->resetForEvent($event);
    }

    /**
     * @param User[] $users
     * @param Event  $event
     */
    public function preloadPlanningHandlerForUsersAndEvent(array $users, Event $event): void
    {
        $this->planningViewQueryHandler->preloadForEventAndUsers($event, $users);
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $userLocale
     *
     * @return PlanningView
     */
    private function getPlanningFromUser(Event $event, User $user, $userLocale): PlanningView
    {
        return $this->planningViewQueryHandler->handle(
            new PlanningViewQuery($event, $user, $userLocale)
        );
    }

    /**
     * Format the planning of the participant from the PlanningView
     *
     * @param PlanningView $planning
     * @param string       $userLocale
     *
     * @return string
     */
    public function formatPlanningFromPlanningView(PlanningView $planning, $userLocale)
    {
        return $this->format($planning->days, $userLocale, $planning->eventTimeZone, $planning->isUserMultipleSheet);
    }

    /**
     * Format the planning of the user with the user's sheets unallocated
     *
     * @param Event        $event
     * @param User         $user
     * @param PlanningView $planningView
     * @param string       $userLocale
     *
     * @return string
     */
    public function formatPlanningFromPlanningViewWithUnallocatedForUser(
        Event $event,
        User $user,
        PlanningView $planningView,
        $userLocale
    ) {
        $planning = $this->format(
            $planningView->days,
            $userLocale,
            $planningView->eventTimeZone,
            $planningView->isUserMultipleSheet
        );
        $unallocated = $this->unallocatedFormatter->formatForUser(
            $event,
            $user,
            $userLocale,
            $planningView->isUserMultipleSheet
        );

        if (empty($unallocated)) {
            return $planning;
        }

        return MarkdownFormatter::newLine($planning) . $unallocated;
    }

    /**
     * @param DayView[] $days
     * @param string    $participantLocale
     * @param string    $timeZone
     * @param bool      $isUserMultipleSheets
     *
     * @return string
     */
    private function format(array $days, $participantLocale, $timeZone, $isUserMultipleSheets = false)
    {
        $formatted    = '';
        $dayFormatter = new \IntlDateFormatter(
            $participantLocale,
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            $timeZone
        );

        foreach ($days as $day) {
            // Sort the time entities of the day by time asc
            $timeEntities = $this->sortChronologicalOrder($day->getTimeEntities());

            // Display the day name
            $formatted .= MarkdownFormatter::newLine(MarkdownFormatter::bold(
                ucfirst($this->getFormattedDate($dayFormatter, $day->getDay()))
            ));

            // Display the happening, mass, unavailability, meeting
            $formatted .= $this->formatTimeEntities(
                $timeEntities,
                $participantLocale,
                $timeZone,
                $isUserMultipleSheets
            );

            if ($day !== end($days)) {
                $formatted .= MarkdownFormatter::newBreak();
            }
        }

        return $formatted;
    }

    /**
     * @param AbstractTimeEntityView[] $timeEntities
     * @param string                   $userLocale
     * @param string                   $timeZone
     * @param bool                     $isUserMultipleSheets
     *
     * @return string
     */
    private function formatTimeEntities(array $timeEntities, $userLocale, $timeZone, $isUserMultipleSheets)
    {
        $formattedTimes = [];
        $timeFormatter  = new \IntlDateFormatter(
            $userLocale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $timeZone
        );

        foreach ($timeEntities as $timeEntity) {
            // Display the time of begin and end of the time entity
            $formatted = $this->translator->trans(
                self::TRANSLATE_TIME_ENTITY,
                [
                    '%beginHour%' => $this->getFormattedDate($timeFormatter, $timeEntity->begin),
                    '%endHour%' => $this->getFormattedDate($timeFormatter, $timeEntity->end),
                ],
                self::TRANSLATION_DOMAIN,
                $userLocale
            );

            // Display the information of the time entity (meeting, mass, unavailability, happening)
            if ($timeEntity instanceof MeetingView) {
                $formatted .= $this->formatMeeting($timeEntity, $userLocale, $isUserMultipleSheets);
            } elseif ($timeEntity instanceof MassView
                || $timeEntity instanceof HappeningParticipationView
                || $timeEntity instanceof AssignmentView
            ) {
                $formatted .= $timeEntity->title;
            } elseif ($timeEntity instanceof UnavailabilityView) {
                if ($timeEntity->hasMessage()) {
                    $formatted .= $timeEntity->message;
                } else {
                    $formatted .= $this->translator
                        ->trans(self::TRANSLATE_UNAVAILABILITY, [], self::TRANSLATION_DOMAIN, $userLocale)
                    ;
                }
            }

            $formattedTimes[] = $formatted;
        }

        return MarkdownFormatter::lists($formattedTimes);
    }

    /**
     * @param MeetingView $meetingView
     * @param string      $userLocale
     * @param bool        $isUserMultipleSheets
     *
     * @return string
     */
    private function formatMeeting(MeetingView $meetingView, $userLocale, $isUserMultipleSheets)
    {
        $sheetsMetTitles = [];
        $sheetsMetViews = $this->linkedSheetsTitle->getSheetMetViews($meetingView->userSheet, $meetingView->sheetMet);

        foreach ($sheetsMetViews as $sheetMetView) {
            $sheetsMetTitles[] = $sheetMetView->isHighlighted()
                ? MarkdownFormatter::bold($sheetMetView->getTitle())
                : $sheetMetView->getTitle();
        }

        $sheetsMetTitle = implode(' - ', $sheetsMetTitles);

        if (true === $isUserMultipleSheets) {
            $meetingTranslation = $this->translator->trans(
                self::TRANSLATE_MEETING_MULTIPLE_SHEET,
                [
                    '%userSheet%' => $meetingView->userSheet->getTitle(),
                    '%sheetMet%' => $sheetsMetTitle,
                    '%spotRef%' => $meetingView->spotRef,
                ],
                self::TRANSLATION_DOMAIN,
                $userLocale
            );
        } else {
            $meetingTranslation = $this->translator->trans(
                self::TRANSLATE_MEETING,
                [
                    '%sheetMet%' => $sheetsMetTitle,
                    '%spotRef%' => $meetingView->spotRef,
                ],
                self::TRANSLATION_DOMAIN,
                $userLocale
            );
        }

        if ($meetingView->hasParticipantsInfo) {
            $meetingTranslation .= ' - ' . implode(
                    ', ',
                    array_map(function (ParticipantMetView $participantMetView) {
                        $completeName = $participantMetView->completeName;
                        $position = $participantMetView->position;

                        return trim(sprintf('%s %s', $completeName, $position));
                    }, $meetingView->participantsMetViews)
                );
        }

        return $meetingTranslation;
    }

    /**
     * @param array $timeEntities
     *
     * @return array
     */
    private function sortChronologicalOrder(array $timeEntities): array
    {
        usort($timeEntities, function (AbstractTimeEntityView $first, AbstractTimeEntityView $second) {
            return $first->begin > $second->begin;
        });

        return $timeEntities;
    }

    /**
     * @param \IntlDateFormatter $formatter
     * @param \DateTimeInterface $date
     *
     * @return string
     */
    private function getFormattedDate(\IntlDateFormatter $formatter, \DateTimeInterface $date): string
    {
        $formatted = $formatter->format($date);

        return !\is_bool($formatted) ? $formatted : '';
    }
}
