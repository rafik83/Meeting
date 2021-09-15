<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use IntlDateFormatter;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

/**
 * Agenda
 */
class PlanningViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var AgendaAccessChecker */
    private $agendaAccessChecker;

    /** @var null|IntlDateFormatter */
    private $formatter = null;

    /** @var MeetingPublishedAccessChecker */
    private $meetingPublishedAccessChecker;

    /**
     * @param NavigationBuilderInterface    $navigationBuilder
     * @param AgendaAccessChecker           $agendaAccessChecker
     * @param MeetingPublishedAccessChecker $meetingPublishedAccessChecker
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        AgendaAccessChecker $agendaAccessChecker,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker
    ) {
        $this->navigationBuilder             = $navigationBuilder;
        $this->agendaAccessChecker           = $agendaAccessChecker;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
    }

    /**
     * @param PlanningViewQuery $planningQuery
     *
     * @return CategoryView
     */
    public function handle(PlanningViewQuery $planningQuery)
    {
        $event     = $planningQuery->sheet->getEvent();
        $linkViews = [];

        $linkViews[] = $this->getAgendaAvailableDateLinkView($event, $planningQuery->sheet, $planningQuery->locale);

        $schedulePublishDateLinkView = $this->getSchedulePublishDateLinkView(
            $event,
            $planningQuery->sheet,
            $planningQuery->locale
        );

        if (null !== $schedulePublishDateLinkView) {
            $linkViews[] = $schedulePublishDateLinkView;
        }

        $categoryTitle = Category::PLANNING;

        if (null !== $planningQuery->staticFormulation) {
            $categoryTitle = $planningQuery->staticFormulation->getTitle($planningQuery->locale);
        }

        return new CategoryView($categoryTitle, Category::PLANNING_ICON, $linkViews, true);
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return LinkView
     */
    private function getAgendaAvailableDateLinkView(Event $event, Sheet $sheet, $locale)
    {
        $agendaOpenDate = $sheet
            ->getEvent()
            ->getConfiguration()
            ->getAgendaOnlineDate();

        if (null !== $agendaOpenDate) {
            $agendaOpenDateFormatted = $this->getFormatter($locale)->format($agendaOpenDate);

            $agendaRoute = null;

            if ($this->agendaAccessChecker->allowedToAccess($event)) {
                $agendaRoute = $this->navigationBuilder->getRoute('event_agenda', ['sheet' => $sheet->getId()]);
            }

            return new LinkView(
                'navigation.links.planning.available_date',
                $agendaRoute,
                null,
                new StateButtonView(false, $agendaOpenDateFormatted ? $agendaOpenDateFormatted : '')
            );
        }

        return new LinkView('navigation.links.incoming', null);
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return null|LinkView
     */
    private function getSchedulePublishDateLinkView(Event $event, Sheet $sheet, $locale)
    {
        $schedulePublishDate = $sheet
            ->getEvent()
            ->getConfiguration()
            ->getSchedulePublishDate();

        if (null !== $schedulePublishDate) {
            $schedulePublishDateFormatted = $this->getFormatter($locale)->format($schedulePublishDate);

            $agendaRoute = null;

            if ($this->meetingPublishedAccessChecker->allowedToAccess($event)) {
                $agendaRoute = $this->navigationBuilder->getRoute('event_agenda', ['sheet' => $sheet->getId()]);
            }

            return new LinkView(
                'navigation.links.planning.final_date',
                $agendaRoute,
                null,
                new StateButtonView(false, $schedulePublishDateFormatted ? $schedulePublishDateFormatted : '')
            );
        }

        return null;
    }

    /**
     * @param string $locale
     *
     * @return IntlDateFormatter
     */
    private function getFormatter($locale)
    {
        if (null !== $this->formatter) {
            return $this->formatter;
        }

        $this->formatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG
        );
        $this->formatter->setPattern('d MMMM Y');

        return $this->formatter;
    }
}
