<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;

class MeetingTypeViewQueryHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * MeetingTypeViewQueryHandler constructor.
     *
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param MeetingTypeViewQuery $query
     *
     * @return TypeView[]
     */
    public function handle(MeetingTypeViewQuery $query)
    {
        $visibleTypes = $this->typeRepository->getFromSheetMeetingRequests($query->sheet, $query->locale);

        $typeViews = [];

        foreach ($visibleTypes as $type) {
            $typeViews[] = new TypeView($type->getId(), $type->getTitle($query->locale), '', $type->isHidden());
        }

        return $typeViews;
    }
}
