<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use DateTimeInterface;
use IntlDateFormatter;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class MeetingViewQueryHandler
{
    /** @var DateTimeInterface */
    private $dateTime;

    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /**
     * @param DateTimeInterface          $dateTime
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(DateTimeInterface $dateTime, NavigationBuilderInterface $navigationBuilder)
    {
        $this->dateTime          = $dateTime;
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param MeetingViewQuery $meetingViewQuery
     *
     * @return CategoryView
     */
    public function handle(MeetingViewQuery $meetingViewQuery)
    {
        $catalogOnlineDate = $meetingViewQuery
            ->sheet
            ->getEvent()
            ->getConfiguration()
            ->getCatalogOnlineDate();

        $linksView = [];

        if (null === $catalogOnlineDate) {
            $linksView[] = new LinkView('navigation.links.incoming', null);
        } elseif ($catalogOnlineDate < $this->dateTime) {
            if (!$meetingViewQuery->sheet->isInInternalCatalog()) {
                // catalog opened but sheet not in catalog
                $linksView[] = new LinkView('navigation.links.catalog.sheet_not_in_catalog');
            } else {
                $linksView[] = new LinkView(
                    'navigation.links.meetingRequest.proposal',
                    $this->navigationBuilder->getRoute('event_meeting_list_request', [
                        'sheet' => $meetingViewQuery->sheet->getId(),
                        'state' => Meeting\Constant::FILTER_STATE_RECEIVE,
                    ]),
                    null,
                    null
                );

                $linksView[] = new LinkView(
                    'navigation.links.meetingRequest.sent',
                    $this->navigationBuilder->getRoute('event_meeting_list_request', [
                        'sheet' => $meetingViewQuery->sheet->getId(),
                        'state' => Meeting\Constant::FILTER_STATE_SENT,
                    ]),
                    null,
                    null
                );

                $linksView[] = new LinkView(
                    'navigation.links.meetingRequest.approved',
                    $this->navigationBuilder->getRoute('event_meeting_list_request', [
                        'sheet' => $meetingViewQuery->sheet->getId(),
                        'state' => Meeting\Constant::FILTER_STATE_APPROVED,
                    ]),
                    null,
                    null
                );

                $linksView[] = new LinkView(
                    'navigation.links.meetingRequest.refused',
                    $this->navigationBuilder->getRoute('event_meeting_list_request', [
                        'sheet' => $meetingViewQuery->sheet->getId(),
                        'state' => Meeting\Constant::FILTER_STATE_REFUSED,
                    ]),
                    null,
                    null
                );
            }
        } else {
            $formatter = new IntlDateFormatter(
                $meetingViewQuery->locale,
                IntlDateFormatter::LONG,
                IntlDateFormatter::LONG
            );
            $formatter->setPattern('d MMMM Y');
            $catalogOnlineDateFormatted = $formatter->format($catalogOnlineDate);

            $linksView[] = new LinkView(
                'navigation.links.meetingRequest.open_date',
                null,
                null,
                new StateButtonView(
                    false,
                    false !== $catalogOnlineDateFormatted ? $catalogOnlineDateFormatted : ''
                )
            );
        }

        $categoryTitle = Category::MEETING;

        if (null !== $meetingViewQuery->staticFormulation) {
            $categoryTitle = $meetingViewQuery->staticFormulation->getTitle($meetingViewQuery->locale);
        }

        return new CategoryView($categoryTitle, Category::MEETING_ICON, $linksView, true);
    }
}
