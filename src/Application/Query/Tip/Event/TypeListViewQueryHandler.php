<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;

class TypeListViewQueryHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /**
     * TypeListViewQueryHandler constructor.
     *
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param TypeListViewQuery $query
     *
     * @return TypeView[]
     */
    public function handle(TypeListViewQuery $query)
    {
        return $this->typeRepository->getTypeViewsByEvent(
            $query->event,
            $query->locale
        );
    }
}
