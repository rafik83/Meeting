<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\SearchFacet;

use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;

class UpdateHandler
{
    /**
     * @var SearchFacetRepositoryInterface
     */
    private $searchFacetRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param SearchFacetRepositoryInterface $searchFacetRepository
     */
    public function __construct(SearchFacetRepositoryInterface $searchFacetRepository)
    {
        $this->searchFacetRepository = $searchFacetRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        foreach ($update->searchFacets as $searchFacet) {

            foreach ($searchFacet->getTranslations() as $locale => $translation) {
                $searchFacet->translate($locale, $translation->getLabel(), $translation->getPlaceholder());
            }

            if (null === $searchFacet->getId()) {
                $this->searchFacetRepository->add($searchFacet);
            } else {
                $this->searchFacetRepository->set($searchFacet);
            }
        }
    }
}
