<?php

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityQueryHandler
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /**
     * CatalogVisibilityQueryHandler constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     */
    public function __construct(CatalogVisibilityRepositoryInterface $catalogVisibilityRepository)
    {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
    }

    /**
     * @param CatalogVisibilityQuery $query
     *
     * @return CatalogVisibility
     */
    public function handle(CatalogVisibilityQuery $query): CatalogVisibility
    {
        if (null === $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($query->event)) {
            $catalogVisibility = new CatalogVisibility($query->event);
        }

        return $catalogVisibility;
    }
}
