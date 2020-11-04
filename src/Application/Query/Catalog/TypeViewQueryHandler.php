<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;

class TypeViewQueryHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @return TypeView[]
     */
    public function handle(TypeViewQuery $query): array
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
