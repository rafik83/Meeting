<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetAccessChecker
{
    /**
     * @var VisibleParticipationTypes
     */
    private $visibleParticipationTypes;

    /**
     * @param VisibleParticipationTypes $visibleParticipationTypes
     */
    public function __construct(VisibleParticipationTypes $visibleParticipationTypes)
    {
        $this->visibleParticipationTypes = $visibleParticipationTypes;
    }

    /**
     * @param Sheet $userSheet
     * @param Sheet $requestedSheet
     *
     * @return bool
     */
    public function checkAccess(Sheet $userSheet, Sheet $requestedSheet)
    {
        $visibleTypes = $this->visibleParticipationTypes->getAllowedTypesList($userSheet);

        return array_key_exists($requestedSheet->getType()->getId(), $visibleTypes);
    }
}
