<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;

class TypeViewQueryHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param TypeViewQuery $query
     *
     * @return TypeView[]
     */
    public function handle(TypeViewQuery $query)
    {
        $types = $this->typeRepository->getTypesTitleByEventAndLocale(
            $query->event,
            $query->locale,
            $query->visibleTypes
        );

        $typeViews = [];

        foreach ($types as $id => $title) {
            $typeViews[$id] = new TypeView($id, $title, 0);
        }

        return $typeViews;
    }
}
