<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Components\Catalog\GetViewedSheetsFromFilters;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Catalog\CatalogAvailableSlotIdsViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\FilteredFieldsQuery;
use Proximum\Vimeet\Application\Query\Catalog\KeywordViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\LocalizationViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedCatalogSheetPreviewViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\FilteredFieldsView;
use Proximum\Vimeet\Domain\Catalog\Catalog;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security\CatalogAccessEventListener;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\AvailabilityConfirmationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViews;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsResult;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Routes are being protected by security access checker
 *
 * @see CatalogAccessEventListener
 */
class CatalogController extends Controller
{
    public function redirectAction(Request $request, EventDomain $eventDomain): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet(
            $this->getUser(),
            $eventDomain->getEvent(),
            $request->getLocale()
        );

        return $this->redirectToRoute('event_catalog_index', ['sheet' => $sheet->getId()]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function indexAction(Request $request, EventDomain $eventDomain, Sheet $sheet, UserDomain $userDomain): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $event  = $eventDomain->getEvent();
        $locale = $request->getLocale();
        $user   = $userDomain->getUser();

        $availabilityConfirmation = $this->get('handler.catalog.availability_confirmation_checker_handler')
            ->handle(new AvailabilityConfirmationChecker(
                $event,
                $sheet,
                $user,
                AvailabilityConfirmationChecker::ORIGIN_CATALOG
            ))
        ;

        if (!$availabilityConfirmation->isAllowedToAccess()) {
            return $this->redirect($availabilityConfirmation->redirectRoute);
        }

        if (!$sheet->isInInternalCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $catalogFilterViewsResult = $this
            ->get(CatalogFilterViewsHandler::class)
            ->handle(new CatalogFilterViews($event, $sheet, $locale))
        ;

        if ($catalogFilterViewsResult->hasEmptyCategoryOrType()) {
            return $catalogFilterViewsResult->response;
        }

        $categoryViews     = $catalogFilterViewsResult->categoryViews;
        $typeViews         = $catalogFilterViewsResult->typeViews;
        $sheetsToExclude   = [];
        $availableSlotsIds = [];

        $filterAvailableSlotAndSpecificSlotChecker = $this
            ->get(FilterAvailableSlotAndSpecificSlotCheckerHandler::class)
            ->handle(new FilterAvailableSlotAndSpecificSlotChecker(
                $event,
                $sheet,
                $user,
                $request->query->get('slot_id')
            ))
        ;

        $filters = $this->getDefaultFilters($typeViews, $categoryViews);

        $searchForm = $this->getSearchForm(
            $filters,
            $catalogFilterViewsResult,
            $event,
            $sheet,
            $locale,
            $filterAvailableSlotAndSpecificSlotChecker->filterAvailableSlot,
            $filterAvailableSlotAndSpecificSlotChecker->specificSlot
        );

        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();

            // if type field is empty, set the default types
            if (empty($filters[SearchFields::FILTER_TYPE])) {
                $filters[SearchFields::FILTER_TYPE] = $typeViews;
            }

            if (empty($filters[SearchFields::FILTER_CATEGORY])) {
                $filters[SearchFields::FILTER_CATEGORY] = $categoryViews;
            }
        }

        if ($filterAvailableSlotAndSpecificSlotChecker->filterAvailableSlot) {
            $catalogAvailableSlotView = $this
                ->get('tactician.commandbus.query')
                ->handle(new CatalogAvailableSlotIdsViewQuery($event, $sheet, $user, $filters))
            ;

            $availableSlotsIds = $catalogAvailableSlotView->availableSlotIds;
            $sheetsToExclude = $catalogAvailableSlotView->sheetsToExclude;
        }

        $page = $request->query->getInt('page', 1);

        $filters = array_merge(Catalog::DEFAULT_FILTERS, $filters);

        $searchFacetView = $this->get('tactician.commandbus.query')->handle(new SearchFacetViewQuery($event, $locale));

        try {
            /** @var PaginatedResult $paginatedResult */
            $paginatedResult = $this->get('tactician.commandbus.query')->handle(
                new PaginatedCatalogSheetPreviewViewQuery(
                    $event,
                    $filters,
                    $page,
                    48,
                    $locale,
                    $sheet,
                    $user,
                    $availableSlotsIds,
                    $sheetsToExclude,
                    $searchFacetView->hasCategory()
                )
            );
        } catch (UnavailableCurrentPageException $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }

        $seeMoreButton = false;

        if ($paginatedResult->total > ($paginatedResult->limit * $paginatedResult->page)) {
            $seeMoreButton = true;
        }

        $searchForm = $this->getFilteredSearchForm(
            $event,
            $sheet,
            $locale,
            $filters,
            $paginatedResult->aggregations,
            $catalogFilterViewsResult,
            $filterAvailableSlotAndSpecificSlotChecker->filterAvailableSlot,
            $filterAvailableSlotAndSpecificSlotChecker->specificSlot,
            $availableSlotsIds,
            $sheetsToExclude,
            $this->get(GetViewedSheetsFromFilters::class)->getFilteredByVisitSheetIds($filters, $user, $sheet)
        );

        if ($request->isXmlHttpRequest()) {
            $template = 'EventBundle:Catalog:Partial/catalog.html.twig';

            if ($page > 1) {
                return new JsonResponse(
                    [
                        'html'          => $this->renderView('EventBundle:Catalog:Partial/list.html.twig', [
                            'paginatedResult' => $paginatedResult,
                            'viewer'          => $sheet,
                            'page'            => $page,
                            'isCatalog'       => true,
                        ]),
                        'seeMoreButton' => $seeMoreButton,
                    ]
                );
            }
        } else {
            $template = 'EventBundle:Catalog:index.html.twig';
        }

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_CATALOG,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        return $this->render($template, [
            'event'               => $event,
            'sheet'               => $sheet,
            'page'                => 1,
            'isCatalog'           => true,
            'typeViews'           => $typeViews,
            'categoryViews'       => $categoryViews,
            'paginatedResult'     => $paginatedResult,
            'seeMoreButton'       => $seeMoreButton,
            'searchForm'          => $searchForm->createView(),
            'tipTranslationViews' => $tipTranslationViews,
        ]);
    }

    /**
     * Get localization asynchronously
     */
    public function searchLocalizationAction(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $query = $request->get('query');

        if (null === $query) {
            return new JsonResponse([]);
        }

        $localizationView = $this->get('tactician.commandbus.query')->handle(
            new LocalizationViewQuery(
                $eventDomain->getEvent(),
                $query,
                Catalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($localizationView);
    }

    public function searchKeywordsAction(Request $request, EventDomain $eventDomain, Sheet $sheet): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $query = $request->get('query');

        if (null === $query) {
            return new JsonResponse([]);
        }

        $keywordView = $this->get('tactician.commandbus.query')->handle(
            new KeywordViewQuery(
                $eventDomain->getEvent(),
                $query,
                Catalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($keywordView);
    }

    /**
     * @param TypeView[]     $typeViews
     * @param CategoryView[] $categoryViews
     *
     * @return array
     */
    private function getDefaultFilters(array $typeViews, array $categoryViews): array
    {
        $filters = [
            SearchFields::ORDER_BY                  => Sheet\Constant::ORDER_BY_RELEVANCE,
            SearchFields::FILTER_TYPE               => $typeViews,
            SearchFields::FILTER_CATEGORY           => $categoryViews,
            SearchFields::FILTER_AVAILABLE_SLOT_IDS => CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_EVERYONE,
        ];

        return $filters;
    }

    /**
     * @param array                    $filters
     * @param CatalogFilterViewsResult $catalogFilterViewsResult
     * @param Event                    $event
     * @param Sheet                    $sheet
     * @param string                   $locale
     * @param bool                     $filterAvailableSlotIds
     * @param MeetingSlot|null         $specificSlot
     *
     * @return FormInterface
     */
    private function getSearchForm(
        array $filters,
        CatalogFilterViewsResult $catalogFilterViewsResult,
        Event $event,
        Sheet $sheet,
        $locale,
        bool $filterAvailableSlotIds = false,
        MeetingSlot $specificSlot = null
    ) {
        return $this->get('form.factory')->createNamed('', SearchType::class, $filters, [
            'action' => $this->generateUrl('event_catalog_index', ['sheet' => $sheet->getId()]),
            'filterBySheetVisit' => $sheet->getType()->canDisplayAnalyticsOnCatalog(),
            'typeViews' => $catalogFilterViewsResult->typeViews,
            'categoryViews' => $catalogFilterViewsResult->categoryViews,
            'organizationCategoryViews' => $catalogFilterViewsResult->organizationCategoryViews,
            'positionViews' => $catalogFilterViewsResult->positionViews,
            'taggedNomenclatureTagViews' => $catalogFilterViewsResult->taggedNomenclatureTagViews,
            'event' => $event,
            'locale' => $locale,
            'filterByAvailableSlotIds' => $filterAvailableSlotIds,
            'filterBySpecificSlot' => null !== $specificSlot,
            'specificSlot' => $specificSlot,
            'objectiveFilters' => $catalogFilterViewsResult->objectiveFilters,
        ]);
    }

    private function getFilteredSearchForm(
        Event $event,
        Sheet $sheet,
        string $locale,
        array $filters,
        array $currentAggregations,
        CatalogFilterViewsResult $catalogFilterViewsResult,
        bool $filterAvailableSlotIds = false,
        MeetingSlot $specificSlot = null,
        array $availableSlotsIds = [],
        array $sheetsToExclude = [],
        ?array $prefilteredSheetIds
    ): FormInterface {
        /** @var FilteredFieldsView $filteredFieldsView */
        $filteredFieldsView = $this->get('tactician.commandbus.query')->handle(
            new FilteredFieldsQuery(
                $event,
                $filters,
                $currentAggregations,
                $catalogFilterViewsResult,
                $locale,
                $availableSlotsIds,
                $sheetsToExclude,
                $prefilteredSheetIds
            )
        );

        return $this->getSearchForm(
            $filters,
            $filteredFieldsView->catalogFilterViewsResult,
            $event,
            $sheet,
            $locale,
            $filterAvailableSlotIds,
            $specificSlot
        );
    }
}
