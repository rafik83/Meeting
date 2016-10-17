<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use DateTimeInterface;
use IntlDateFormatter;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class HappeningViewQueryHandler
{
    /**
     * @var DateTimeInterface
     */
    private $dateTime;

    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * CatalogViewQueryHandler constructor.
     *
     * @param DateTimeInterface          $dateTime
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(DateTimeInterface $dateTime, NavigationBuilderInterface $navigationBuilder)
    {
        $this->dateTime          = $dateTime;
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param HappeningViewQuery $happeningViewQuery
     *
     * @return CategoryView
     */
    public function handle(HappeningViewQuery $happeningViewQuery)
    {
        $happeningOpenDate = $happeningViewQuery
            ->sheet
            ->getEvent()
            ->getConfiguration()
            ->getHappeningsOpenDate();

        $linksView = [];

        if ($happeningOpenDate === null) {
            $linksView[] = new LinkView('navigation.links.incoming', null);
        } elseif ($happeningOpenDate < $this->dateTime) {

            $linksView[] = new LinkView(
                'navigation.links.happening.proposal',
                $this->navigationBuilder->getRoute('event_meeting_list_request', [
                    'sheet' => $happeningViewQuery->sheet->getId(),
                ]),
                null,
                null
            );

            $linksView[] = new LinkView(
                'navigation.links.happening.sent',
                $this->navigationBuilder->getRoute('event_meeting_list_request', [
                    'sheet' => $happeningViewQuery->sheet->getId(),
                    'state' => Request::STATE_SENT
                ]),
                null,
                null
            );

            $linksView[] = new LinkView(
                'navigation.links.happening.approved',
                $this->navigationBuilder->getRoute('event_meeting_list_request', [
                    'sheet' => $happeningViewQuery->sheet->getId(),
                    'state' => Request::STATE_APPROVED
                ]),
                null,
                null
            );

            $linksView[] = new LinkView(
                'navigation.links.happening.refused',
                $this->navigationBuilder->getRoute('event_meeting_list_request', [
                    'sheet' => $happeningViewQuery->sheet->getId(),
                    'state' => Request::STATE_REFUSED
                ]),
                null,
                null
            );

        } else {
            $formatter = new IntlDateFormatter(
                $happeningViewQuery->locale,
                IntlDateFormatter::LONG,
                IntlDateFormatter::LONG
            );
            $formatter->setPattern('d MMMM Y');
            $happeningOpenDateFormatted = $formatter->format($happeningOpenDate);

            $linksView[] = new LinkView(
                'navigation.links.happening.open_date',
                null,
                null,
                new StateButtonView(false, $happeningOpenDateFormatted ? $happeningOpenDateFormatted : '')
            );
        }

        return new CategoryView(Category::HAPPENING, Category::HAPPENING_ICON, $linksView);
    }
}
