<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeViewQueryHandler
{
    /**
     * @var TypeRepositoryInterface
     */
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
        $typeViews = [];
        $types     = $this->typeRepository->getTypesByEvent($query->event);

        foreach ($types as $type) {
            $typeViews[] = new TypeView(
                $type->getId(),
                $type->getTitle($query->locale)
            );
        }

        return $typeViews;
    }
}
