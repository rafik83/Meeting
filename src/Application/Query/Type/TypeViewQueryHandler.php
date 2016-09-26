<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeListsView;
use Proximum\Vimeet\Domain\View\TypeListView;

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
    public function handle(TypeViewQuery $query)
    {
        $typeResults = $this->typeRepository->paginate(
            $query->page,
            20,
            $query->event->getId(),
            $query->event->getAvailableLocale($query->locale)
        );

        $typeListsView = new TypeListsView();

        /** @var Type $type */
        foreach ($typeResults as $type) {
            $typeListsView->types[] = new TypeListView(
                $type->getId(),
                $type->getPosition(),
                $type->getTitle($query->locale),
                $type->isHidden(),
                $type->getRegistrationTemplate()->getTitle(),
                $type->getSheetTemplate()->getTitle(),
                $type->getPackage()->getTitle()
            );
        }

        $typeListsView->results = $typeResults;

        return $typeListsView;
    }
}
