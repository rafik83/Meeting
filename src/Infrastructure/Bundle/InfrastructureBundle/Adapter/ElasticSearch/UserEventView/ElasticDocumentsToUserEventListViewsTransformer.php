<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;

class ElasticDocumentsToUserEventListViewsTranformer
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param \Elastica\Document[] $documents
     * @param string               $locale
     *
     * @return UserEventListView[]
     */
    public function handle(array $documents, string $locale): array
    {
        $sheetIds = [];

        foreach ($documents as $document) {
            $data = $document->getData();

            foreach ($data['sheets'] as $sheetData) {
                $id = $sheetData['id'];
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

            $userEventListViews[] = new UserEventListView(
                $data['eventId'],
                $data['userId'],
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['locale'],
                array_map(
                    function ($sheetData) use ($sheetsIndexedById) {
                        return $sheetsIndexedById[$sheetData['id']] ?? null;
                    },
                    $data['sheets']
                )
            );
        }

        return $userEventListViews;
    }
}
