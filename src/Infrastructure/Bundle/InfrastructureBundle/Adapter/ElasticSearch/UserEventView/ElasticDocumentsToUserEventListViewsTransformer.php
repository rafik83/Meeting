<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Elastica\Document;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Domain\UserEventView\UserEventSheetsListView;

class ElasticDocumentsToUserEventListViewsTransformer
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * @param Document[] $documents
     * @param string     $locale
     *
     * @return UserEventListView[]
     */
    public function handle(array $documents, string $locale): array
    {
        $sheetIds = [];

        foreach ($documents as $document) {
            $data = $document->getData();

            foreach ($data[TypesMapping::USER_EVENT_VIEW_SHEETS] as $sheetData) {
                $id = $sheetData[TypesMapping::USER_EVENT_VIEW_SHEETS_ID];
                $sheetIds[$id] = $id;
            }
        }

        $sheetsIndexedById = $this->sheetRepository->getSheetsByIdsWithTypesAndCategories(
            array_values($sheetIds),
            $locale
        );
        $userEventListViews = [];

        foreach ($documents as $document) {
            $data = $document->getData();

            $extraDataVisioTested = $this->extraDataRepository->getExtraDataForEventIdNameAndUserId(
                $data[TypesMapping::USER_EVENT_VIEW_EVENT_ID],
                Type::VISIO_TESTED,
                $data[TypesMapping::USER_EVENT_VIEW_USER_ID]
            );

            $userEventSheetsListViews = [];

            foreach ($data['sheets'] as $sheetData) {
                if (isset($sheetsIndexedById[$sheetData['id']])) {
                    $sheet = $sheetsIndexedById[$sheetData['id']];
                    $participant = $sheet->getParticipantByUserId($data['userId']);

                    $userEventSheetsListViews[] = new UserEventSheetsListView(
                        $sheet->getId(),
                        $sheet->getTitle(),
                        $data['userId'] === $sheet->getOwnerId(),
                        $sheet->getTypeTitle($locale),
                        $sheet->getCategoriesTitles($locale),
                        $sheet->isEnabled(),
                        $sheet->getState(),
                        $sheet->getValidationState(),
                        $sheet->getCompleteness(),
                        Sheet::getCompletenessStatus($sheet->getCompleteness()),
                        $sheet->attend(),
                        $sheet->hasGroup(),
                        $sheet->getGroupTitle(),
                        $sheet->isInInternalCatalog(),
                        $sheet->getFollowerName(),
                        $sheet->getCommercialStatus(),
                        $sheet->getCommercialStatusLabel(),
                        $participant ? $participant->isVisio() : false,
                        $extraDataVisioTested instanceof ExtraData ? (bool)$extraDataVisioTested->getValue() : false
                    );
                }
            }

            if (!empty($userEventSheetsListViews)) {
                $userEventListViews[] = new UserEventListView(
                    $data[TypesMapping::USER_EVENT_VIEW_EVENT_ID],
                    $data[TypesMapping::USER_EVENT_VIEW_USER_ID],
                    $data[TypesMapping::USER_EVENT_VIEW_FIRSTNAME],
                    $data[TypesMapping::USER_EVENT_VIEW_LASTNAME],
                    $data[TypesMapping::USER_EVENT_VIEW_EMAIL],
                    $data[TypesMapping::USER_EVENT_VIEW_LOCALE],
                    $userEventSheetsListViews
                );
            }
        }

        return $userEventListViews;
    }
}
