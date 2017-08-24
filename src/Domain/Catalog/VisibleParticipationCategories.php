<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class VisibleParticipationCategories
{
    /** @var RuleRepositoryInterface */
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
     * @return Category[]
     */
    public function getAllowedCategoriesList(Sheet $sheet)
    {
        $visibleCategories = [];
        $filteredRules     = [];
        $categoryTypes     = $sheet->getType()->getCategories();
        $rules             = $this->ruleRepository->getByEvent($sheet->getEvent());

        foreach ($categoryTypes as $categoryType) {
            $filteredRules = array_filter($rules, function (Rule $rule) use ($categoryType) {
                return $rule->getSeerCategory() === $categoryType;
            });
        }

        foreach ($filteredRules as $rule) {
            /** @var Category $categoryType */
            foreach ($rule->getSeeableCategory() as $categoryType) {
                $visibleCategories[$categoryType->getId()] = $categoryType;
            }
        }

        return $visibleCategories;
    }
}
