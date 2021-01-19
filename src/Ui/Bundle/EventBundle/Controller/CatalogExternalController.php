<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityMessageQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityRegistrationUrlQuery;
use Proximum\Vimeet\Application\Query\Catalog\KeywordViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\LocalizationViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetExternalViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Catalog\PaginatedSheetExternalViewQuery;
use Proximum\Vimeet\Domain\Catalog\ExternalCatalog;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Exception\Catalog\CatalogVisibilityNotFoundException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogExternalController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain): Response
    {
        $event   = $eventDomain->getEvent();
        $locale  = $request->getLocale();
        $page    = $request->query->getInt('page', 1);
        $filters = [];

        try {
            $searchFacetsView = $this->get('query.catalog.search_facet_external_view_query_handler')->handle(
                new SearchFacetExternalViewQuery($event, $locale)
            );

            $searchForm = $this->get('form_factory.search_facet_external_factory')
                ->create($event, $locale, $filters, $searchFacetsView);

            $categoryViews = $searchFacetsView->hasCategory()
                ? $this->get('form_factory.search_facet_external_factory')->getCategoryViews($event, $locale)
                : null;

            $typeViews = $searchFacetsView->hasType()
                ? $this->get('form_factory.search_facet_external_factory')->getTypeViews($event, $locale)
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
            $paginatedResult = $this->get('tactician.commandbus.query')->handle(
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

        $searchForm = $this->get('form_factory.search_facet_external_factory')
            ->createFiltered($event, $locale, $filters, $paginatedResult->aggregations, $searchFacetsView);
        $message = $this->get('tactician.commandbus.query')->handle(
            new CatalogVisibilityMessageQuery($event, $locale)
        );

        $registrationUrl = $this->get('tactician.commandbus.query')->handle(
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
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return JsonResponse
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

        $localizationView = $this->get('tactician.commandbus.query')->handle(
            new LocalizationViewQuery(
                $eventDomain->getEvent(),
                $query,
                ExternalCatalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($localizationView);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return JsonResponse
     */
    public function searchKeywordsAction(Request $request, EventDomain $eventDomain): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }

        $query = $request->get('query');

        if (null === $query) {
            return new JsonResponse([]);
        }

        $keywordView = $this->get('tactician.commandbus.query')->handle(
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
