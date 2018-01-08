<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Catalog\CatalogAvailableSlotIdsViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetsViewQuery;
use Proximum\Vimeet\Domain\Catalog\Catalog;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CategoryTypeOrganizationAndPositionViews;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CategoryTypeOrganizationAndPositionViewsHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CatalogAccessChecker */
    private $catalogAccessChecker;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var CategoryTypeOrganizationAndPositionViewsHandler */
    private $categoryTypeOrganizationAndPositionViewsHandler;

    /** @var FilterAvailableSlotAndSpecificSlotCheckerHandler */
    private $filterAvailableSlotAndSpecificSlotCheckerHandler;

    /**
     * @param AuthorizationCheckerAdapterInterface             $authorizationCheckerAdapter
     * @param CatalogAccessChecker                             $catalogAccessChecker
     * @param FormFactoryInterface                             $formFactory
     * @param QueryBusInterface                                $queryBus
     * @param CategoryTypeOrganizationAndPositionViewsHandler  $categoryTypeOrganizationAndPositionViewsHandler
     * @param FilterAvailableSlotAndSpecificSlotCheckerHandler $filterAvailableSlotAndSpecificSlotCheckerHandler
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CatalogAccessChecker $catalogAccessChecker,
        FormFactoryInterface $formFactory,
        QueryBusInterface $queryBus,
        CategoryTypeOrganizationAndPositionViewsHandler $categoryTypeOrganizationAndPositionViewsHandler,
        FilterAvailableSlotAndSpecificSlotCheckerHandler $filterAvailableSlotAndSpecificSlotCheckerHandler
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->catalogAccessChecker = $catalogAccessChecker;
        $this->formFactory = $formFactory;
        $this->queryBus = $queryBus;
        $this->categoryTypeOrganizationAndPositionViewsHandler = $categoryTypeOrganizationAndPositionViewsHandler;
        $this->filterAvailableSlotAndSpecificSlotCheckerHandler = $filterAvailableSlotAndSpecificSlotCheckerHandler;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param UserDomain  $userDomain
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, EventDomain $eventDomain, Sheet $sheet, UserDomain $userDomain)
    {
        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$sheet->isInCatalog()
            || !$this->catalogAccessChecker->allowedToAccess($event)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $filters = [];

        $categoryTypeOrganizationPositionViews = $this->categoryTypeOrganizationAndPositionViewsHandler
            ->handle(new CategoryTypeOrganizationAndPositionViews($event, $sheet, $locale))
        ;

        if ($categoryTypeOrganizationPositionViews->hasEmptyCategoryOrType()) {
            throw new NotFoundHttpException('Not found category or type');
        }

        $categoryViews             = $categoryTypeOrganizationPositionViews->categoryViews;
        $typeViews                 = $categoryTypeOrganizationPositionViews->typeViews;
        $organizationCategoryViews = $categoryTypeOrganizationPositionViews->organizationCategoryViews;
        $positionViews             = $categoryTypeOrganizationPositionViews->positionViews;
        $availableSlotsIds         = [];
        $sheetsToExclude           = [];

        $filterAvailableSlotAndSpecificSlotChecker = $this
            ->filterAvailableSlotAndSpecificSlotCheckerHandler
            ->handle(new FilterAvailableSlotAndSpecificSlotChecker(
                $event,
                $sheet,
                $userDomain->getUser(),
                $request->query->get('slot_id')
            ))
        ;

        $form = $this->formFactory->createNamed('', SearchType::class, $filters, [
            'typeViews'                 => $typeViews,
            'categoryViews'             => $categoryViews,
            'organizationCategoryViews' => $organizationCategoryViews,
            'positionViews'             => $positionViews,
            'event'                     => $event,
            'locale'                    => $locale,
            'filterByAvailableSlotIds'  => $filterAvailableSlotAndSpecificSlotChecker->filterAvailableSlot,
            'filterBySpecificSlot'      => $filterAvailableSlotAndSpecificSlotChecker->specificSlot !== null,
            'specificSlot'              => $filterAvailableSlotAndSpecificSlotChecker->specificSlot,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();

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
                ->queryBus
                ->handle(new CatalogAvailableSlotIdsViewQuery($event, $sheet, $userDomain->getUser(), $filters))
            ;

            $availableSlotsIds = $catalogAvailableSlotView->availableSlotIds;
            $sheetsToExclude = $catalogAvailableSlotView->sheetsToExclude;
        }

        $filters = array_merge(
            Catalog::DEFAULT_FILTERS,
            $filters,
            [SearchFields::ORDER_BY => Constant::ORDER_BY_ALPHABETICAL]
        );

        $sheets = $this->queryBus->handle(new SheetsViewQuery(
            $event,
            $sheet,
            $filters,
            $locale,
            $availableSlotsIds,
            $sheetsToExclude
        ));

        return new JsonResponse('test');
    }
}
