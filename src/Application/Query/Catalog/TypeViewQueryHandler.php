<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;

class TypeViewQueryHandler
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
     * @param TypeViewQuery $query
     *
     * @return TypeView[]
     */
    public function handle(TypeViewQuery $query)
    {
        $types      = $this->typeRepository->getTypesTitleByEventAndLocale(
            $query->event,
            $query->locale,
            $query->visibleTypes
        );
        $typesCount = $this->sheetSearchAdapter->getTypeStats(
            $query->event,
            array_merge(['inCatalog' => true], $query->filters)
        );

        $typeViews = [];

        foreach ($types as $id => $title) {
            $typeViews[$id] = new TypeView($id, $title, isset($typesCount[$id]) ? $typesCount[$id] : 0);
        }

        return $typeViews;
    }
}
