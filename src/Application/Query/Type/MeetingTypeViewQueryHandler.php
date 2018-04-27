<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\View\TypeView;

class MeetingTypeViewQueryHandler
{
    /**
     * @var VisibleParticipationTypes
     */
    private $visibleParticipationTypes;

    /**
     * MeetingTypeViewQueryHandler constructor.
     *
     * @param VisibleParticipationTypes $visibleParticipationTypes
     */
    public function __construct(VisibleParticipationTypes $visibleParticipationTypes)
    {
        $this->visibleParticipationTypes = $visibleParticipationTypes;
    }

    /**
     * @param MeetingTypeViewQuery $query
     *
     * @return TypeView[]
     */
    public function handle(MeetingTypeViewQuery $query)
    {
        $visibleTypes = $this->visibleParticipationTypes->getAllowedTypesList($query->sheet);

        $typeViews = [];

        foreach ($visibleTypes as $type) {
            $typeViews[] = new TypeView($type->getId(), $type->getTitle($query->locale), '');
        }

        return $typeViews;
    }
}
