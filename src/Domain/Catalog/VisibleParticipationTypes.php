<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class VisibleParticipationTypes
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    public function getAllowedTypesList(Sheet $sheet)
    {
        $visibleTypes = [];
        $type = $sheet->getType();

        $rules = $this->ruleRepository->getByEvent($sheet->getEvent());

        $filteredTypes = array_filter($rules, function (Rule $rule) use ($type) {
            if ($rule->getSeerType() == $type) {
                return $rule->getSeeableType();
            }
            return false;
        });

        foreach ($filteredTypes as $type) {
            $visibleTypes[$type->getSeeableType()->getId()] = $type;
        }

        return $visibleTypes;
    }
}
