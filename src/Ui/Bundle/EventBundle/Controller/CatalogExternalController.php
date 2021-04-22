<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityMessageQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityRegistrationUrlQuery;
use Proximum\Vimeet\Application\Query\Catalog\KeywordViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\LocalizationViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetExternalViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetExternalViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Catalog\PaginatedSheetExternalViewQuery;
use Proximum\Vimeet\Domain\Catalog\ExternalCatalog;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Exception\Catalog\CatalogVisibilityNotFoundException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Factory\SearchFacetExternalFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogExternalController extends AbstractController
{
    private SearchFacetExternalViewQueryHandler $searchFacetExternalViewQueryHandler;
    private SearchFacetExternalFactory $searchFacetExternalFactory;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        SearchFacetExternalViewQueryHandler $searchFacetExternalViewQueryHandler,
        SearchFacetExternalFactory $searchFacetExternalFactory,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->searchFacetExternalViewQueryHandler = $searchFacetExternalViewQueryHandler;
        $this->searchFacetExternalFactory = $searchFacetExternalFactory;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function indexAction(Request $request, EventDomain $eventDomain): Response
    {
        $event   = $eventDomain->getEvent();
        $locale  = $request->getLocale();
        $page    = $request->query->getInt('page', 1);
        $filters = [];

        try {
            $searchFacetsView = $this->searchFacetExternalViewQueryHandler->handle(
                new SearchFacetExternalViewQuery($event, $locale)
            );

            $searchForm = $this->searchFacetExternalFactory
                ->create($event, $locale, $filters, $searchFacetsView);

            $categoryViews = $searchFacetsView->hasCategory()
                ? $this->searchFacetExternalFactory->getCategoryViews($event, $locale)
                : null;

            $typeViews = $searchFacetsView->hasType()
                ? $this->searchFacetExternalFactory->getTypeViews($event, $locale)
                : null
            ;

            $filters[SearchFields::FILTER_TYPE] = $typeViews;
            $filters[SearchFields::FILTER_CATEGORY] = $categoryViews;
        } catch (CatalogVisibilityNotFoundException $exception) {
            throw $this->createNotFoundException();
        }

        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();

            // if type field is empty, set the default types
            if (empty($filters[SearchFields::FILTER_TYPE])) {
                $filters[SearchFields::FILTER_TYPE] = $typeViews;
            }

            // if type field is empty, set the default types
            if (empty($filters[SearchFields::FILTER_CATEGORY])) {
                $filters[SearchFields::FILTER_CATEGORY] = $categoryViews;
            }
        }

        $filters = array_merge($filters, ExternalCatalog::DEFAULT_FILTERS);

        try {
            $paginatedResult = $this->queryBus->handle(
                new PaginatedSheetExternalViewQuery(
                    $event,
                    $filters,
                    $page,
                    48,
                    $request->getLocale(),
                    $searchFacetsView->hasCategory()
                )
            );
        } catch (UnavailableCurrentPageException $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }

        $searchForm = $this->searchFacetExternalFactory
            ->createFiltered($event, $locale, $filters, $paginatedResult->aggregations, $searchFacetsView);
        $message = $this->queryBus->handle(
            new CatalogVisibilityMessageQuery($event, $locale)
        );

        $registrationUrl = $this->queryBus->handle(
            new CatalogVisibilityRegistrationUrlQuery($event)
        );

        $seeMoreButtonStatus = $paginatedResult->total > ($paginatedResult->limit * $paginatedResult->page);

        if ($request->isXmlHttpRequest()) {
            $template = 'EventBundle:Catalog:External/catalog.html.twig';

            if ($page > 1) {
                return new JsonResponse(
                    [
                        'html'          => $this->renderView('EventBundle:Catalog/External:list.html.twig', [
                            'paginatedResult' => $paginatedResult,
                            'page'            => $page,
                        ]),
                        'seeMoreButton' => $seeMoreButtonStatus,
                    ]
                );
            }
        } else {
            $template = 'EventBundle:Catalog:External/index.html.twig';
        }

        return $this->render($template, [
            'event'             => $event,
            'page'              => 1,
            'paginatedResult'   => $paginatedResult,
            'seeMoreButton'     => $seeMoreButtonStatus,
            'searchForm'        => $searchForm->createView(),
            'catalogOnlineDate' => $event->getConfiguration()->getCatalogOnlineDate(),
            'typeViews'         => $typeViews,
            'categoryViews'     => $categoryViews,
            'message'           => $message,
            'registrationUrl'   => $registrationUrl,
        ]);
    }

    /**
     * Get localization asynchronously
     */
    public function searchLocalizationAction(Request $request, EventDomain $eventDomain): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }

        $query = $request->get('query');

        if (null === $query) {
            return new JsonResponse([]);
        }

        $localizationView = $this->queryBus->handle(
            new LocalizationViewQuery(
                $eventDomain->getEvent(),
                $query,
                ExternalCatalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($localizationView);
    }

    public function searchKeywordsAction(Request $request, EventDomain $eventDomain): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }

        $query = $request->get('query');

        if (null === $query) {
            return new JsonResponse([]);
        }

        $keywordView = $this->queryBus->handle(
            new KeywordViewQuery(
                $eventDomain->getEvent(),
                $query,
                ExternalCatalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($keywordView);
    }
}
