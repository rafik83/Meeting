<?php

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Application\View\Type\TypesWithPaymentConditionsView;
use Proximum\Vimeet\Application\View\Type\TypeWithPaymentConditionsView;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypesWithPaymentConditionsViewQueryHandler
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
     * @param TypesWithPaymentConditionsViewQuery $query
     *
     * @return TypesWithPaymentConditionsView
     */
    public function handle(TypesWithPaymentConditionsViewQuery $query): TypesWithPaymentConditionsView
    {
        $typeViews = [];

        $types = $this->typeRepository->getTypesWithPaymentConditionsByEvent($query->event);

        foreach ($types as $type) {
            $typeViews[] = new TypeWithPaymentConditionsView($type->getTitle($query->locale));
        }

        return new TypesWithPaymentConditionsView($typeViews);
    }
}
