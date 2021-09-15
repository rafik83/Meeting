<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Catalog\CategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\NomenclatureTagViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Domain\Catalog\GetDisplayObjectiveFilter;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationCategories;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\Catalog\SheetVisitView;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CatalogFilterViewsHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var VisibleParticipationCategories */
    private $visibleParticipationCategories;

    /** @var VisibleParticipationTypes */
    private $visibleParticipationTypes;

    /** @var Environment */
    private $twig;

    /** @var GetDisplayObjectiveFilter */
    private $getDisplayObjectiveFilter;

    public function __construct(
        QueryBusInterface $queryBus,
        VisibleParticipationCategories $visibleParticipationCategories,
        VisibleParticipationTypes $visibleParticipationTypes,
        Environment $twig,
        GetDisplayObjectiveFilter $getDisplayObjectiveFilter
    ) {
        $this->queryBus = $queryBus;
        $this->visibleParticipationCategories = $visibleParticipationCategories;
        $this->visibleParticipationTypes = $visibleParticipationTypes;
        $this->twig = $twig;
        $this->getDisplayObjectiveFilter = $getDisplayObjectiveFilter;
    }

    public function handle(
        CatalogFilterViews $catalogFilterViews,
        bool $hasSheetVisitFilter = false,
        User $user = null
    ): CatalogFilterViewsResult {
        $event = $catalogFilterViews->event;
        $sheet = $catalogFilterViews->sheet;
        $locale = $catalogFilterViews->locale;

        /** @var SearchFacetsView $searchFacetsView */
        $searchFacetsView = $this->queryBus->handle(
            new SearchFacetViewQuery($event, $locale)
        );

        $sheetVisitViews = [];
        $categoryViews = [];
        $typeViews = [];
        $organizationCategoryViews = [];
        $positionViews = [];
        $taggedNomenclatureTagViews = [];

        if ($hasSheetVisitFilter) {
            $sheetVisitViews[CatalogConstant::FILTER_SHEET_SAW] = new SheetVisitView(CatalogConstant::FILTER_SHEET_SAW, 0, $user, $sheet);
            $sheetVisitViews[CatalogConstant::FILTER_VIEWED_BY_SHEET] = new SheetVisitView(CatalogConstant::FILTER_VIEWED_BY_SHEET, 0, null, $sheet);
        }

        if ($searchFacetsView->hasCategory()) {
            $visibleCategories = $this
                ->visibleParticipationCategories
                ->getAllowedCategoriesList($sheet);

            if (!empty($visibleCategories)) {
                $categoryViews = $this->queryBus->handle(new CategoryViewQuery($event, $visibleCategories, $locale));
            }
        }

        if (empty($visibleCategories)) {
            $visibleTypes = $this->visibleParticipationTypes->getAllowedTypesList($sheet);

            if (empty($visibleTypes)) {
                return $this->getEmptyCategoryOrTypeCatalogFilterViewResult($event, $sheet);
            }

            $typeViews = $this->queryBus->handle(new TypeViewQuery($event, $visibleTypes, $locale));
        }

        if (null !== $searchFacetsView->getOrganizationCategory()) {
            $organizationCategoryViews = $this->queryBus->handle(new OrganizationCategoryViewQuery($event, $locale));
        }

        if (null !== $searchFacetsView->getPosition()) {
            $positionViews = $this->queryBus->handle(new PositionViewQuery($event, $locale));
        }

        $tagFilterViews = $searchFacetsView->getTagFilterViews();

        if (!empty($tagFilterViews)) {
            $taggedNomenclatureTagViews = $this->queryBus->handle(
                new NomenclatureTagViewQuery($event, array_keys($tagFilterViews), $locale)
            );
        }

        return new CatalogFilterViewsResult(
            CatalogFilterViewsResult::RESULT_CATEGORY_OR_TYPE,
            $sheetVisitViews,
            $categoryViews,
            $typeViews,
            $organizationCategoryViews,
            $positionViews,
            $taggedNomenclatureTagViews,
            null,
            ($this->getDisplayObjectiveFilter)($sheet, $locale)
        );
    }

    public function getEmptyCategoryOrTypeCatalogFilterViewResult(Event $event, Sheet $sheet): CatalogFilterViewsResult
    {
        return new CatalogFilterViewsResult(
            CatalogFilterViewsResult::EMPTY_CATEGORY_OR_TYPE,
            [],
            [],
            [],
            [],
            [],
            [],
            new Response($this->twig->render(
                'EventBundle:Catalog:no-visible-type.html.twig',
                ['event' => $event, 'sheet' => $sheet]
            )),
            []
        );
    }
}
