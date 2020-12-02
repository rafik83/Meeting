<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Catalog\CanSeeOtherSheets;

class CatalogSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * @var DateTimeInterface
     */
    private $datetime;

    /** @var CanSeeOtherSheets */
    private $canSeeOtherSheets;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /**
     * CatalogSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param DateTimeInterface          $datetime
     * @param CanSeeOtherSheets          $canSeeOtherSheets
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        DateTimeInterface $datetime,
        CanSeeOtherSheets $canSeeOtherSheets,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->datetime = $datetime;
        $this->canSeeOtherSheets = $canSeeOtherSheets;
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param CatalogSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(CatalogSubmenuViewQuery $query)
    {

        $catalogOnlineDate = $query->event->getConfiguration()->getCatalogOnlineDate();

        if (null == $catalogOnlineDate || !$query->sheet->isInInternalCatalog() || $catalogOnlineDate > $this->datetime) {
            return [];
        }

        $buttonViews = [];

        if ($this->canSeeOtherSheets->isSatisfiedBy($query->sheet)) {
            $catalogTitle = 'navigation.category.catalog';

            if (isset($query->staticFormulationsIndexedByCategory[Category::CATALOG])) {
                $catalogTitle = $query->staticFormulationsIndexedByCategory[Category::CATALOG]->getTitle($query->locale);
            }

            $buttonViews[] = new SubmenuButtonView(
                Category::CATALOG_ICON,
                $catalogTitle,
                $this->navigationBuilder->getRoute('event_catalog_index', ['sheet' => $query->sheet->getId()]),
                Route::isCatalog($query->route),
                null,
                true
            );
        }

        $meetingTitle = 'navigation.category.meeting';

        if (isset($query->staticFormulationsIndexedByCategory[Category::MEETING])) {
            $meetingTitle = $query->staticFormulationsIndexedByCategory[Category::MEETING]->getTitle($query->locale);
        }

        $pendingRequestCount = $this->requestRepository->countPendingPropositionReceivedBySheet($query->sheet);

        $queryParams = [
            'sheet' => $query->sheet->getId(),

        ];

        if (null != $pendingRequestCount && $pendingRequestCount > 0) {
            $queryParams["state"] = "receive";
        }

        $buttonViews[] = new SubmenuButtonView(
            Category::MEETING_ICON,
            $meetingTitle,
            $this->navigationBuilder->getRoute(
                $query->sheet->hasLinkedSheets()
                    ? 'event_meeting_request_merged_list'
                    : 'event_meeting_list_request',
                $queryParams

            ),
            Route::isMeetingRequest($query->route),
            $pendingRequestCount,
            false
        );

        return $buttonViews;
    }
}
