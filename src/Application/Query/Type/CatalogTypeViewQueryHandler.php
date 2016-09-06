<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;

class CatalogTypeViewQueryHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * @param TypeRepositoryInterface     $typeRepository
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter
    ) {
        $this->typeRepository     = $typeRepository;
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param CatalogTypeViewQuery $query
     *
     * @return TypeView[]
     */
    public function handle(CatalogTypeViewQuery $query)
    {
        $types      = $this->typeRepository->getTypesTitleByEventAndLocale($query->event, $query->locale);
        $typesCount = $this->sheetSearchAdapter->getTypeStats($query->event, $query->filters);

        $typeViews = [];

        foreach ($types as $id => $title) {
            $count = 0;

            foreach ($typesCount as $typeCount) {
                if ($typeCount['key'] === $id) {
                    $count = $typeCount['doc_count'];
                    break;
                }
            }

            $typeViews[] = new TypeView($id, $title, $count);
        }

        return $typeViews;
    }
}
