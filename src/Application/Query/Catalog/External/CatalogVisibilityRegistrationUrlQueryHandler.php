<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityRegistrationUrlQueryHandler
{
    /**
     * @var CatalogVisibilityRepositoryInterface
     */
    private $catalogVisibilityRepository;

    /**
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     */
    public function __construct(CatalogVisibilityRepositoryInterface $catalogVisibilityRepository)
    {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
    }

    /**
     * @param CatalogVisibilityRegistrationUrlQuery $query
     *
     * @return null|string
     */
    public function handle(CatalogVisibilityRegistrationUrlQuery $query): ?string
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($query->event);

        return $catalogVisibility !== null ? $catalogVisibility->getRegistrationUrl() : null;
    }
}
