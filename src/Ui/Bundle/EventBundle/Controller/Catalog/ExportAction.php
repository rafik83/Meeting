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
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Catalog\CatalogAvailableSlotIdsViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetsViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Catalog\Catalog;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViews;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
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

    /** @var CatalogFilterViewsHandler */
    private $catalogFilterViewsHandler;

    /** @var FilterAvailableSlotAndSpecificSlotCheckerHandler */
    private $filterAvailableSlotAndSpecificSlotCheckerHandler;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param AuthorizationCheckerAdapterInterface             $authorizationCheckerAdapter
     * @param CatalogAccessChecker                             $catalogAccessChecker
     * @param FormFactoryInterface                             $formFactory
     * @param QueryBusInterface                                $queryBus
     * @param CatalogFilterViewsHandler                        $catalogFilterViewsHandler
     * @param FilterAvailableSlotAndSpecificSlotCheckerHandler $filterAvailableSlotAndSpecificSlotCheckerHandler
     * @param SerializerAdapterInterface                       $serializerAdapter
     * @param \DateTimeInterface                               $dateTime
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CatalogAccessChecker $catalogAccessChecker,
        FormFactoryInterface $formFactory,
        QueryBusInterface $queryBus,
        CatalogFilterViewsHandler $catalogFilterViewsHandler,
        FilterAvailableSlotAndSpecificSlotCheckerHandler $filterAvailableSlotAndSpecificSlotCheckerHandler,
        SerializerAdapterInterface $serializerAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->catalogAccessChecker = $catalogAccessChecker;
        $this->formFactory = $formFactory;
        $this->queryBus = $queryBus;
        $this->catalogFilterViewsHandler = $catalogFilterViewsHandler;
        $this->filterAvailableSlotAndSpecificSlotCheckerHandler = $filterAvailableSlotAndSpecificSlotCheckerHandler;
        $this->serializerAdapter = $serializerAdapter;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param UserDomain  $userDomain
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return CsvFileResponse
     */
    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain
    ): CsvFileResponse {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();
        $locale = $request->getLocale();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$sheet->isInInternalCatalog()
            || !$this->catalogAccessChecker->allowedToAccess($event)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $catalogFilterViewsResult = $this->catalogFilterViewsHandler
            ->handle(new CatalogFilterViews($event, $sheet, $locale))
        ;

        if ($catalogFilterViewsResult->hasEmptyCategoryOrType()) {
            throw new NotFoundHttpException('Not found category or type');
        }

        $categoryViews             = $catalogFilterViewsResult->categoryViews;
        $typeViews                 = $catalogFilterViewsResult->typeViews;
        $availableSlotsIds         = [];
        $sheetsToExclude           = [];

        $filters = [
            SearchFields::FILTER_TYPE               => $typeViews,
            SearchFields::FILTER_CATEGORY           => $categoryViews,
            SearchFields::FILTER_AVAILABLE_SLOT_IDS => CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_EVERYONE,
        ];

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
            'filterBySheetVisit' => $sheet->getType()->displayAnalyticsOnCatalog,
            'typeViews' => $catalogFilterViewsResult->typeViews,
            'categoryViews' => $catalogFilterViewsResult->categoryViews,
            'organizationCategoryViews' => $catalogFilterViewsResult->organizationCategoryViews,
            'taggedNomenclatureTagViews' => $catalogFilterViewsResult->taggedNomenclatureTagViews,
            'positionViews' => $catalogFilterViewsResult->positionViews,
            'event' => $event,
            'locale' => $locale,
            'filterByAvailableSlotIds' => $filterAvailableSlotAndSpecificSlotChecker->filterAvailableSlot,
            'filterBySpecificSlot' => null !== $filterAvailableSlotAndSpecificSlotChecker->specificSlot,
            'specificSlot' => $filterAvailableSlotAndSpecificSlotChecker->specificSlot,
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

        if (true === $filterAvailableSlotAndSpecificSlotChecker->filterAvailableSlot) {
            $catalogAvailableSlotView = $this
                ->queryBus
                ->handle(new CatalogAvailableSlotIdsViewQuery($event, $sheet, $user, $filters))
            ;

            $availableSlotsIds = $catalogAvailableSlotView->availableSlotIds;
            $sheetsToExclude = $catalogAvailableSlotView->sheetsToExclude;
        }

        $filters = array_merge(
            Catalog::DEFAULT_FILTERS,
            $filters,
            [SearchFields::ORDER_BY => Constant::ORDER_BY_ALPHABETICAL]
        );

        $sheetListView = $this->queryBus->handle(new SheetsViewQuery(
            $event,
            $sheet,
            $user,
            $filters,
            $locale,
            $availableSlotsIds,
            $sheetsToExclude,
            empty($categoryViews)
        ));
        $content = $this->serializerAdapter->serialize($sheetListView, 'csv', [
            'charset'       => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ]);

        return new CsvFileResponse(
            substr($content, strpos($content, "\n") + 1), // Remove first line of the file that contains the keys
            sprintf('export_catalog_selection_%s.csv', $this->dateTime->format('Y_m_d_His'))
        );
    }
}
