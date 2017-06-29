<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Application\View\Catalog\External\CatalogVisibilityView;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityViewQueryHandler
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /**
     * CatalogVisibilityViewQueryHandler constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     */
    public function __construct(CatalogVisibilityRepositoryInterface $catalogVisibilityRepository)
    {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
    }

    /**
     * @param CatalogVisibilityViewQuery $query
     *
     * @return CatalogVisibilityView
     */
    public function handle(CatalogVisibilityViewQuery $query): CatalogVisibilityView
    {
        if (null === $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($query->event)) {
            $catalogVisibility = new CatalogVisibility($query->event);
        }

        return new CatalogVisibilityView(
            $catalogVisibility->getEvent(),
            $catalogVisibility->getTypes(),
            $catalogVisibility->getCategories()
        );
    }
}
