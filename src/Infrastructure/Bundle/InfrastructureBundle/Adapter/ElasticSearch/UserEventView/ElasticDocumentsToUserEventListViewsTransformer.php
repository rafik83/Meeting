<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Elastica\Document;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Domain\UserEventView\UserEventSheetsListView;

class ElasticDocumentsToUserEventListViewsTransformer
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
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
            $userId = $data[TypesMapping::USER_EVENT_VIEW_USER_ID];
            $userEventSheetsListViews = [];

            foreach ($data[TypesMapping::USER_EVENT_VIEW_SHEETS] as $sheetData) {
                $id = $sheetData[TypesMapping::USER_EVENT_VIEW_SHEETS_ID];

                if (!isset($sheetsIndexedById[$id])) {
                    continue;
                }

                $sheet = $sheetsIndexedById[$id];

                $userEventSheetsListViews[] = new UserEventSheetsListView(
                    $sheet->getId(),
                    $sheet->getTitle(),
                    $userId === $sheet->getOwnerId(),
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
                    $sheet->getCommercialStatusLabel()
                );
            }

            if (!empty($userEventSheetsListViews)) {
                $userEventListViews[] = new UserEventListView(
                    $data[TypesMapping::USER_EVENT_VIEW_EVENT_ID],
                    $data[TypesMapping::USER_EVENT_VIEW_USER_ID],
                    $data[TypesMapping::USER_EVENT_VIEW_FIRSTNAME],
                    $data[TypesMapping::USER_EVENT_VIEW_LASTNAME],
                    $data[TypesMapping::USER_EVENT_VIEW_EMAIL],
                    $data[TypesMapping::USER_EVENT_VIEW_LOCALE],
                    $data[TypesMapping::USER_EVENT_VIEW_IS_VISIO],
                    $data[TypesMapping::USER_EVENT_VIEW_IS_VISIO_TESTED],
                    $userEventSheetsListViews
                );
            }
        }

        return $userEventListViews;
    }
}
