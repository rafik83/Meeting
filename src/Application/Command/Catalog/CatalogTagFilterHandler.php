<?php

namespace Proximum\Vimeet\Application\Command\Catalog;

use Proximum\Vimeet\Application\Command\Catalog\External\ConfigureSearchFacet;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilterTranslation;
use Proximum\Vimeet\Domain\Repository\Catalog\CatalogTagFilterRepositoryInterface;

class CatalogTagFilterHandler
{
    /** @var CatalogTagFilterRepositoryInterface */
    private $catalogTagFilterRepository;

    public function __construct(CatalogTagFilterRepositoryInterface $catalogTagFilterRepository)
    {
        $this->catalogTagFilterRepository = $catalogTagFilterRepository;
    }

    public function handle(ConfigureSearchFacet $configureSearchFacet): void
    {
        $this->catalogTagFilterRepository->removeByEventAndType(
            $configureSearchFacet->event,
            $configureSearchFacet->type
        );

        foreach ($configureSearchFacet->catalogTagFilters as $tagFilter) {
            $catalogTagFilter = new CatalogTagFilter(
                $configureSearchFacet->event,
                $tagFilter['tag'],
                $configureSearchFacet->type
            );

            foreach ($tagFilter['translations'] as $locale => $translation) {
                $catalogTagFilter->addTranslation(
                    new CatalogTagFilterTranslation(
                        $locale,
                        $translation['label'],
                        $translation['placeholder']
                    )
                );
            }

            $this->catalogTagFilterRepository->add($catalogTagFilter);
        }
    }
}
