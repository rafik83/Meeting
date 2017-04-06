<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use IntlDateFormatter;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

/**
 * Agenda
 */
class PlanningViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * @var HappeningsAccessChecker
     */
    private $happeningsAccessChecker;

    /**
     * @var null|IntlDateFormatter
     */
    private $formatter = null;

    /**
     * @var MeetingPublishedAccessChecker
     */
    private $meetingPublishedAccessChecker;

    /**
     * PlanningViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface    $navigationBuilder
     * @param HappeningsAccessChecker       $happeningsAccessChecker
     * @param MeetingPublishedAccessChecker $meetingPublishedAccessChecker
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        HappeningsAccessChecker $happeningsAccessChecker,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker
    ) {
        $this->navigationBuilder             = $navigationBuilder;
        $this->happeningsAccessChecker       = $happeningsAccessChecker;
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

        $linkViews[] = $this->getHappeningAvailableDateLinkView($event, $planningQuery->sheet, $planningQuery->locale);

        $schedulePublishDateLinkView = $this->getSchedulePublishDateLinkView(
            $event,
            $planningQuery->sheet,
            $planningQuery->locale
        );

        if (null !== $schedulePublishDateLinkView) {
            $linkViews[] = $schedulePublishDateLinkView;
        }

        return new CategoryView(Category::PLANNING, Category::PLANNING_ICON, $linkViews);
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return LinkView
     */
    private function getHappeningAvailableDateLinkView(Event $event, Sheet $sheet, $locale)
    {
        $happeningOpenDate = $sheet
            ->getEvent()
            ->getConfiguration()
            ->getHappeningsOpenDate();

        if (null !== $happeningOpenDate) {
            $happeningOpenDateFormatted = $this->getFormatter($locale)->format($happeningOpenDate);

            $agendaRoute = null;

            if ($this->happeningsAccessChecker->allowedToAccess($event)) {
                $agendaRoute = $this->navigationBuilder->getRoute('event_agenda');
            }

            return new LinkView(
                'navigation.links.planning.available_date',
                $agendaRoute,
                null,
                new StateButtonView(false, $happeningOpenDateFormatted ? $happeningOpenDateFormatted : '')
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

        if ($schedulePublishDate !== null) {
            $schedulePublishDateFormatted = $this->getFormatter($locale)->format($schedulePublishDate);

            $agendaRoute = null;

            if ($this->meetingPublishedAccessChecker->allowedToAccess($event)) {
                $agendaRoute = $this->navigationBuilder->getRoute('event_agenda');
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
