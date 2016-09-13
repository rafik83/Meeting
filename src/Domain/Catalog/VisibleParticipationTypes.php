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
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class VisibleParticipationTypes
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param RuleRepositoryInterface     $ruleRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->categoryRepository = $categoryRepository;
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
                return true;
            }
            return false;
        });

        foreach ($rules as $rule) {
            if (!empty($rule->getSeerCategory())) {
                foreach ($rule->getSeerCategory()->getTypes() as $categoryType) {
                    if ($categoryType == $type) {
                        $filteredTypes[] = $rule;
                    }
                }
            }
        }

        foreach ($filteredTypes as $rule) {

            if (!empty($rule->getSeeableCategory())) {
                foreach ($rule->getSeeableCategory()->getTypes() as $categoryType) {
                    $visibleTypes[$categoryType->getId()] = $categoryType;
                }
            }

            $visibleTypes[$rule->getSeeableType()->getId()] = $rule;
        }

        return $visibleTypes;
    }
}
