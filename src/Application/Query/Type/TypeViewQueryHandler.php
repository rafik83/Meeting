<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Application\View\Type\TypeListsView;
use Proximum\Vimeet\Application\View\Type\TypeListView;

class TypeViewQueryHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * TypeViewQueryHandler constructor.
     *
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param TypeViewQuery $query
     *
     * @return TypeListsView
     */
    public function handle(TypeViewQuery $query): TypeListsView
    {
        $typeResults = $this->typeRepository->paginate(
            $query->page,
            20,
            $query->event->getId(),
            $query->locale
        );

        $typeListsView = new TypeListsView();

        /** @var Type $type */
        foreach ($typeResults as $type) {
            $typeListsView->types[] = new TypeListView(
                $type->getId(),
                $type->getPosition(),
                $type->getTitle($query->locale),
                $type->isHidden(),
                (null !== $type->getRegistrationTemplate()) ? $type->getRegistrationTemplate()->getTitle() : '',
                (null !== $type->getSheetTemplate()) ? $type->getSheetTemplate()->getTitle() : '',
                (null !== $type->getPackage()) ? $type->getPackage()->getTitle() : '',
                null !== $type->getPaymentConditions()
            );
        }

        $typeListsView->results = $typeResults;

        return $typeListsView;
    }
}
