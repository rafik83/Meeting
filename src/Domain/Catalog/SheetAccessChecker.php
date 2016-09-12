<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Exception\Catalog\SheetAccessDeniedException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class SheetAccessChecker
{
    /**
    * @var RuleRepositoryInterface
    */
    private $ruleRepository;

    /**
     * @var VisibleParticipationTypes
     */
    private $visibleParticipationTypes;

    /**
     * @param VisibleParticipationTypes $visibleParticipationTypes
     * @param RuleRepositoryInterface   $ruleRepository
     */
    public function __construct(VisibleParticipationTypes $visibleParticipationTypes, RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
        $this->visibleParticipationTypes = $visibleParticipationTypes;
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function checkAccess(Sheet $sheet)
    {
        $visibleTypes = $this->visibleParticipationTypes->getAllowedTypesList($sheet);

        if (empty($visibleTypes)) {
            return true;
        }

        $isSheetVisible = array_key_exists($sheet->getType(), $visibleTypes);

        if (!$isSheetVisible) {
            throw new SheetAccessDeniedException();
        }

        return $isSheetVisible;
    }
}
