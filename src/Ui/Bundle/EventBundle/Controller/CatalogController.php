<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Sheet\SheetViewed\Add;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Catalog\CategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\FilteredFieldsQuery;
use Proximum\Vimeet\Application\Query\Catalog\KeywordViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\LocalizationViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedCatalogSheetPreviewViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\FilteredFieldsView;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\Catalog\Catalog;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security\CatalogAccessEventListener;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CatalogController
 *
 * Routes are being protected by security access checker
 *
 * @see CatalogAccessEventListener
 */
class CatalogController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function redirectAction(Request $request, EventDomain $eventDomain)
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
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function indexAction(Request $request, EventDomain $eventDomain, Sheet $sheet, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $visibleTypes = $this->get('catalog.visible_participation_types')->getAllowedTypesList($sheet);
        $visibleCategories = $this
            ->get('catalog.visible_participation_categories')
            ->getAllowedCategoriesList($sheet);

        if (empty($visibleTypes)) {
            return $this->render(
                'EventBundle:Catalog:no-visible-type.html.twig',
                ['event' => $event, 'sheet' => $sheet]
            );
        }

        $typeViews = $this->get('tactician.commandbus.query')->handle(
            new TypeViewQuery($event, $visibleTypes, $locale)
        );

        $categoryViews = $this->get('tactician.commandbus.query')->handle(
            new CategoryViewQuery($event, $visibleCategories, $locale)
        );

        $organizationCategoryViews = $this->get('tactician.commandbus.query')->handle(
            new OrganizationCategoryViewQuery($event, $locale)
        );

        $positionViews = $this->get('tactician.commandbus.query')->handle(
            new PositionViewQuery($event, $locale)
        );

        $filters = $this->getDefaultFilters($typeViews);

        $searchForm = $this->getSearchForm(
            $filters,
            $typeViews,
            $categoryViews,
            $organizationCategoryViews,
            $positionViews,
            $event,
            $sheet,
            $locale
        );

        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();

            // if type field is empty, set the default types
            if (empty($filters[SearchFields::FILTER_TYPE])) {
                $filters[SearchFields::FILTER_TYPE] = $typeViews;
            }
        }

        $page = $request->query->getInt('page', 1);

        $filters = array_merge(Catalog::DEFAULT_FILTERS, $filters);

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
                    $user
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
            $typeViews,
            $categoryViews,
            $organizationCategoryViews,
            $positionViews
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
            $sheet->getType(),
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
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function searchLocalizationAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $localizationView = $this->get('tactician.commandbus.query')->handle(
            new LocalizationViewQuery(
                $eventDomain->getEvent(),
                $request->get('query'),
                Catalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($localizationView);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return JsonResponse
     */
    public function searchKeywordsAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $keywordView = $this->get('tactician.commandbus.query')->handle(
            new KeywordViewQuery(
                $eventDomain->getEvent(),
                $request->get('query'),
                Catalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($keywordView);
    }

    /**
     * Display a sheet.
     *
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param int           $sheetToDisplay id of Sheet
     *
     * @param UserInterface $user
     *
     * @return Response
     */
    public function displaySheetAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        $sheetToDisplay,
        UserInterface $user
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $event = $eventDomain->getEvent();

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $sheetToDisplay = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetById($sheetToDisplay);

        if (null === $sheetToDisplay || $event !== $sheetToDisplay->getEvent()) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        if (!$sheetToDisplay->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet to display not in catalog');
        }

        $markSheetAsViewedByCurrentUser = new Add($user, $sheetToDisplay);
        $this->get('command.sheet.sheet_viewed.add_handler')->handle($markSheetAsViewedByCurrentUser);

        $locale = $request->getLocale();

        $rules = $this
            ->get('repository.rule_repository')
            ->getBySeerTypeAndSeeableType($sheet->getType(), $sheetToDisplay->getType());

        if (empty($rules)) {
            throw $this->createNotFoundException('You do not have the right to see this sheet');
        }

        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $sheetToDisplay,
            $user,
            $locale
        );

        // Build sheet template data and attach tagged data view to template object with tags
        $templateData = $this->get('template.tagged_data_factory')
            ->buildTaggedDataView($sheetToDisplay, $locale, $rules);

        $ruleApplyer = $this->get('domain.rule.applyer');
        $ruleApplyer->applyRuleForTemplate($templateData, $rules);
        $ruleApplyer->applyRuleForCardList($participants, $rules);

        $isMeetingPublished           = false;
        $isMeetingRequestUpdateLocked = false;
        $isMeetingRequestClosed       = false;
        $isAnsweringMeetingRequestClosed = false;

        if ($sheet === $sheetToDisplay) {
            $meetingRequest = null;
        } else {
            $meetingRequest = $this
                ->get('vimeet_infrastructure.repository.meeting.request_repository')
                ->getRequestBetweenSheets($sheetToDisplay, $sheet);

            $isMeetingPublished = $this
                ->get('domain.key_dates.checker.meeting_published_access_checker')
                ->allowedToAccess($event);

            $isMeetingRequestUpdateLocked = $event->getConfiguration()->isMeetingRequestUpdateLocked();
            $isMeetingRequestClosed          = !$this->get('domain.key_dates.checker.meeting_request_access_checker')->allowedToAccess($event);
            $isAnsweringMeetingRequestClosed = !$this
                ->get('domain.key_dates.checker.answering_meeting_request_access_checker')
                ->allowedToAccess($event)
            ;
        }

        return $this->render('EventBundle:Catalog:displaySheet.html.twig', [
            'event'                           => $event,
            'sheet'                           => $sheet,
            'sheetToDisplay'                  => $sheetToDisplay,
            'taggedData'                      => $taggedData,
            'locale'                          => $locale,
            'nomenclatures'                   => $nomenclatures,
            'participants'                    => $participants,
            'templateData'                    => $templateData,
            'meetingRequest'                  => $meetingRequest,
            'isMeetingPublished'              => $isMeetingPublished,
            'isMeetingRequestUpdateLocked'    => $isMeetingRequestUpdateLocked,
            'isMeetingRequestClosed'          => $isMeetingRequestClosed,
            'isAnsweringMeetingRequestClosed' => $isAnsweringMeetingRequestClosed,
            'isRequestMeetingEnabled'         => $sheet !== $sheetToDisplay,
            'isCatalog'                       => true,
        ]);
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param Sheet  $sheetToDisplay
     * @param User   $user
     * @param string $locale
     *
     * @return array
     */
    private function sheetInfos(Event $event, Sheet $sheet, Sheet $sheetToDisplay, User $user, $locale)
    {
        if (!$this->get('catalog.sheet_access_checker')->checkAccess($sheet, $sheetToDisplay)) {
            throw $this->createAccessDeniedException();
        }

        $nomenclatures     = $this->get('repository.nomenclature_repository')->findByEvent($event);
        $cardListViewQuery = new CardListViewQuery($sheetToDisplay, $user, $locale);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        $registrationTemplateData = $this
            ->get('template.template_data_factory')
            ->createRegistrationFromSheet($sheetToDisplay, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return [$nomenclatures, $participants, $taggedData];
    }

    /**
     * @param TypeView[] $typeViews
     *
     * @return array
     */
    private function getDefaultFilters(array $typeViews)
    {
        $filters = [
            SearchFields::ORDER_BY    => Sheet\Constant::ORDER_BY_RELEVANCE,
            SearchFields::FILTER_TYPE => $typeViews,
        ];

        return $filters;
    }

    /**
     * @param array                      $filters
     * @param TypeView[]                 $typeViews
     * @param CategoryView[]             $categoryViews
     * @param OrganizationCategoryView[] $organizationCategoryViews
     * @param PositionView[]             $positionViews
     * @param Event                      $event
     * @param Sheet                      $sheet
     * @param string                     $locale
     *
     * @return FormInterface
     */
    private function getSearchForm(
        array $filters,
        array $typeViews,
        array $categoryViews,
        array $organizationCategoryViews,
        array $positionViews,
        Event $event,
        Sheet $sheet,
        $locale
    ) {
        return $this->get('form.factory')->createNamed('', SearchType::class, $filters, [
            'action'                    => $this->generateUrl('event_catalog_index', ['sheet' => $sheet->getId()]),
            'typeViews'                 => $typeViews,
            'categoryViews'             => $categoryViews,
            'organizationCategoryViews' => $organizationCategoryViews,
            'positionViews'             => $positionViews,
            'event'                     => $event,
            'locale'                    => $locale,
        ]);
    }

    /**
     * @param Event                      $event
     * @param Sheet                      $sheet
     * @param string                     $locale
     * @param array                      $filters
     * @param array                      $currentAggregations
     * @param TypeView[]                 $typeViews
     * @param CategoryView[]                 $categoryViews
     * @param OrganizationCategoryView[] $organizationCategoryViews
     * @param PositionView[]             $positionViews
     *
     * @return FormInterface
     */
    private function getFilteredSearchForm(
        Event $event,
        Sheet $sheet,
        $locale,
        array $filters,
        array $currentAggregations,
        array $typeViews,
        array $categoryViews,
        array $organizationCategoryViews,
        array $positionViews
    ) {
        /** @var FilteredFieldsView $filteredFieldsView */
        $filteredFieldsView = $this->get('tactician.commandbus.query')->handle(
            new FilteredFieldsQuery(
                $event,
                $filters,
                $currentAggregations,
                $typeViews,
                $categoryViews,
                $organizationCategoryViews,
                $positionViews,
                $locale
            )
        );

        return $this->getSearchForm(
            $filters,
            $filteredFieldsView->typeViews,
            $filteredFieldsView->categoryViews,
            $filteredFieldsView->organizationCategoryViews,
            $filteredFieldsView->positionViews,
            $event,
            $sheet,
            $locale
        );
    }
}
